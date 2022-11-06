import {createApp} from 'vue'
import Media from './App.vue'

const media = createApp({})
media.component('Media', Media)
media.mount('#ocms-media-app');