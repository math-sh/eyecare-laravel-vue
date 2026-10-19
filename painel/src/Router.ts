import Vue from 'vue'
import VueRouter, { type RouteConfig } from 'vue-router'

Vue.use(VueRouter)

const routes: RouteConfig[] = [
  { path: '/', redirect: { name: 'patients.list' } },
  {
    path: '/pacientes',
    name: 'patients.list',
    component: () => import('@/pages/patients/index.vue'),
  },
  {
    path: '/pacientes/novo',
    name: 'patients.create',
    component: () => import('@/pages/patients/form.vue'),
  },
  {
    path: '/pacientes/:id',
    name: 'patients.detail',
    props: true,
    component: () => import('@/pages/patients/detail.vue'),
  },
  {
    path: '/pacientes/:id/editar',
    name: 'patients.edit',
    props: true,
    component: () => import('@/pages/patients/form.vue'),
  },
  { path: '*', redirect: { name: 'patients.list' } },
]

export default new VueRouter({ mode: 'history', routes })
