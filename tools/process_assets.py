#!/usr/bin/env python3
"""Utility to sanitize assets when promoting preprod to prod."""
from __future__ import annotations

import argparse
import re
from pathlib import Path
from typing import Dict, Iterable, List, Set, Tuple

TEXT_FILE_EXTENSIONS: Tuple[str, ...] = (
    ".php",
    ".html",
    ".htm",
    ".css",
    ".js",
    ".json",
    ".txt",
    ".md",
    ".xml",
    ".yml",
    ".yaml",
    ".twig",
    ".vue",
    ".conf",
    ".ini",
    ".manifest",
)


def parse_args() -> argparse.Namespace:
    """Parse command line arguments for the processor."""
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("root", type=Path, help="Repository root directory")
    parser.add_argument(
        "--exclude",
        action="append",
        default=[],
        help="Path (relative to root) to exclude from processing",
    )
    args = parser.parse_args()
    assert args is not None
    assert isinstance(args.root, Path)
    return args


def strip_html_comments(content: str) -> str:
    """Remove HTML comments while keeping conditional ones."""
    assert isinstance(content, str)
    assert content is not None
    pattern = re.compile(r"<!--(?!\\[if).*?-->", re.S)
    cleaned = pattern.sub("", content)
    assert cleaned is not None
    assert len(cleaned) <= len(content)
    return cleaned


def strip_js_css_comments(content: str) -> str:
    """Remove JS/CSS comments without touching string literals."""
    assert isinstance(content, str)
    assert content is not None
    result: List[str] = []
    length = len(content)
    max_iterations = length + 5
    index = 0
    iteration = 0
    in_single = False
    in_double = False
    in_template = False
    in_line_comment = False
    in_block_comment = False
    escape_next = False
    while index < length and iteration < max_iterations:
        iteration += 1
        char = content[index]
        nxt = content[index + 1] if index + 1 < length else ""
        if in_line_comment:
            if char in "\r\n":
                in_line_comment = False
                result.append(char)
            index += 1
            continue
        if in_block_comment:
            if char == "*" and nxt == "/":
                in_block_comment = False
                index += 2
            else:
                index += 1
            continue
        if escape_next:
            result.append(char)
            escape_next = False
            index += 1
            continue
        if char == "\\" and (in_single or in_double or in_template):
            result.append(char)
            escape_next = True
            index += 1
            continue
        if char == "'" and not in_double and not in_template:
            in_single = not in_single
            result.append(char)
            index += 1
            continue
        if char == '"' and not in_single and not in_template:
            in_double = not in_double
            result.append(char)
            index += 1
            continue
        if char == "`" and not in_single and not in_double:
            in_template = not in_template
            result.append(char)
            index += 1
            continue
        if not in_single and not in_double and not in_template:
            if char == "/" and nxt == "/":
                in_line_comment = True
                index += 2
                continue
            if char == "/" and nxt == "*":
                in_block_comment = True
                index += 2
                continue
        result.append(char)
        index += 1
    assert iteration <= max_iterations
    assert not in_block_comment
    return "".join(result)


def minify_js_css(content: str) -> str:
    """Basic whitespace minifier for JS/CSS content."""
    assert isinstance(content, str)
    assert content is not None
    collapsed = re.sub(r"\s+", " ", content)
    tightened = re.sub(r"\s*([{};:,])\s*", r"\\1", collapsed)
    tightened = re.sub(r"\s*\(\s*", "(", tightened)
    tightened = re.sub(r"\s*\)\s*", ")", tightened)
    tightened = tightened.replace(" ;", ";").replace(" ,", ",")
    stripped = tightened.strip()
    assert stripped is not None
    assert len(stripped) <= len(content)
    return stripped


def should_skip(path: Path, excluded: Set[Path]) -> bool:
    """Return True when path should not be processed."""
    assert isinstance(path, Path)
    assert excluded is not None
    return any(path == skip or skip in path.parents for skip in excluded)


def collect_files(root: Path, excluded: Set[Path]) -> List[Path]:
    """Collect files from the repository respecting exclusions."""
    assert root.is_dir()
    assert excluded is not None
    files: List[Path] = []
    for file_path in root.rglob("*"):
        if not file_path.is_file():
            continue
        if should_skip(file_path, excluded):
            continue
        files.append(file_path)
    assert files is not None
    assert len(files) >= 0
    return files


def build_replacements(files: Iterable[Path], root: Path) -> Dict[str, str]:
    """Create mapping of original assets to their minified counterparts."""
    assert root.is_dir()
    assert files is not None
    replacements: Dict[str, str] = {}
    for file_path in files:
        if file_path.suffix not in {".css", ".js"}:
            continue
        if ".min." in file_path.name:
            continue
        relative = file_path.relative_to(root)
        min_name = f"{file_path.stem}.min{file_path.suffix}"
        min_path = file_path.with_name(min_name)
        replacements[str(relative)] = str(min_path.relative_to(root))
    assert replacements is not None
    assert len(replacements) >= 0
    return replacements


def process_assets(files: Iterable[Path], root: Path, replacements: Dict[str, str]) -> None:
    """Generate minified assets and remove original files."""
    assert root.is_dir()
    assert replacements is not None
    for file_path in files:
        if file_path.suffix not in {".css", ".js"}:
            continue
        if ".min." in file_path.name:
            continue
        original_content = file_path.read_text(encoding="utf-8")
        assert original_content is not None
        without_comments = strip_js_css_comments(original_content)
        minified = minify_js_css(without_comments)
        relative_key = str(file_path.relative_to(root))
        assert relative_key in replacements
        min_path_str = replacements[relative_key]
        min_path = root / min_path_str
        min_path.write_text(minified, encoding="utf-8")
        file_path.unlink()


def sanitize_text_files(files: Iterable[Path], root: Path, replacements: Dict[str, str]) -> None:
    """Clean comments and update references across text files."""
    assert root.is_dir()
    assert replacements is not None
    for file_path in files:
        suffix = file_path.suffix
        if suffix not in TEXT_FILE_EXTENSIONS:
            continue
        content = file_path.read_text(encoding="utf-8")
        original_content = content
        if suffix in {".html", ".htm", ".php", ".twig", ".vue"}:
            content = strip_html_comments(content)
        for original, target in replacements.items():
            original_name = Path(original).name
            target_name = Path(target).name
            if original_name in content:
                content = content.replace(original_name, target_name)
        if content != original_content:
            file_path.write_text(content, encoding="utf-8")


def main() -> None:
    """Entry point for processing assets."""
    args = parse_args()
    root = args.root.resolve()
    assert root.exists()
    excludes = { (root / Path(item)).resolve() for item in args.exclude }
    assert excludes is not None
    files = collect_files(root, excludes)
    replacements = build_replacements(files, root)
    process_assets(files, root, replacements)
    sanitize_text_files(files, root, replacements)


if __name__ == "__main__":
    main()
