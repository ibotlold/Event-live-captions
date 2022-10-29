import React from 'react';
import logo from './logo.svg';
import './App.css';

function App() {
  return (
    <div className="App">
      <header className="App-header">
        <img src={logo} className="App-logo" alt="logo" />
        <p>
          Web app provides live captions through Chrome's Google Cloud Speech API.
        </p>
        <p>
          Using React as frontend.
        </p>
      </header>
    </div>
  );
}

export default App;
