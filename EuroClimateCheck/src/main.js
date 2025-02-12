import './assets/main.css'

import { createApp } from 'vue'
import App from './App.vue'
import PrimeVue from 'primevue/config'
import Aura from '@primevue/themes/aura'

// Create a function to mount the app
window.mountEuroClimateCheck = (element) => {
  console.log('Mounting Vue app to:', element);
  const app = createApp(App)
  
  // Use PrimeVue
  app.use(PrimeVue, {
    theme: {
      preset: Aura
    }
  })
  
  // Mount the app
  app.mount(element)
  console.log('Vue app mounted successfully');
}
