<?php
declare(strict_types=1);

$pageTitle = 'Classements | FC Chiché';

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/templates/header.php';
?>
      <section class="section">
        <div class="container">
          <div class="section__header">
            <span class="section__eyebrow">Compétitions</span>
            <h1 class="section__title">Classements officiels</h1>
            <p class="section__subtitle">Suivi des divisions où évoluent les équipes du FC Chiché.</p>
          </div>

          <div class="table-shell" role="region" aria-label="Classement départemental">
            <table class="table">
              <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Équipe</th>
                  <th scope="col">Pts</th>
                  <th scope="col">J</th>
                  <th scope="col">Diff</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>1</td>
                  <td>AUBINRORTHAIS ES</td>
                  <td>40</td>
                  <td>22</td>
                  <td>+18</td>
                </tr>
                <tr>
                  <td>2</td>
                  <td>L’Absie Larg. Mout.</td>
                  <td>37</td>
                  <td>22</td>
                  <td>+3</td>
                </tr>
                <tr>
                  <td>3</td>
                  <td>Faye Noirt. ES</td>
                  <td>34</td>
                  <td>22</td>
                  <td>+2</td>
                </tr>
                <tr>
                  <td>4</td>
                  <td>FC Chiché</td>
                  <td>34</td>
                  <td>22</td>
                  <td>+9</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

<?php
require_once __DIR__ . '/templates/footer.php';
