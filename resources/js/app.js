import { createApp } from 'vue';
import App from './components/App.vue';
import './echo';

/*
| Точка входа Vite. Vue-компонент с уведомлением монтируется
| в <div id="app"> из layouts/app.blade.php.
*/
const container = document.getElementById('app');

if (container) {
    createApp(App).mount(container);
}
