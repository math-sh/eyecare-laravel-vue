/// <reference types="vite/client" />

declare module '*.vue' {
  import type Vue from 'vue'
  const component: typeof Vue
  export default component
}

interface ImportMetaEnv {
  readonly VITE_API_URL?: string
}
