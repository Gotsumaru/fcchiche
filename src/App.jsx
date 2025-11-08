import { useState, useEffect } from 'react'
import Header from './components/Header'
import Footer from './components/Footer'
import HomePage from './pages/HomePage'

export default function App() {
  return (
    <div className="page-shell">
      <Header />
      <div className="page-shell__content page-body page-index">
        <HomePage />
      </div>
      <Footer />
    </div>
  )
}
