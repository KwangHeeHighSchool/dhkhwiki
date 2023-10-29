// Register service worker to control making site work offline

if ('serviceWorker' in navigator) {
  navigator.serviceWorker
    .register('/mediawiki-1.38.4/sw.js')
    .then(() => { console.log('Service Worker Registered'); });
}