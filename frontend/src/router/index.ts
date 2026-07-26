import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  { path: '/', redirect: '/connexion' },
  {
    path: '/connexion',
    name: 'login',
    component: () => import('../views/auth/Login.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/inscription',
    name: 'register',
    component: () => import('../views/auth/Register.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/patient/tableau-de-bord',
    name: 'patient-dashboard',
    component: () => import('../views/patient/Dashboard.vue'),
    meta: { requiresAuth: true, role: 'patient' },
  },
  {
    path: '/patient/reserver',
    name: 'patient-reserver',
    component: () => import('../views/patient/Reserver.vue'),
    meta: { requiresAuth: true, role: 'patient' },
  },
  {
    path: '/medecin/tableau-de-bord',
    name: 'medecin-dashboard',
    component: () => import('../views/medecin/Dashboard.vue'),
    meta: { requiresAuth: true, role: 'medecin' },
  },
  {
    path: '/medecin/creneaux',
    name: 'medecin-creneaux',
    component: () => import('../views/medecin/Creneaux.vue'),
    meta: { requiresAuth: true, role: 'medecin' },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return { name: 'login' }
  }

  if (to.meta.guestOnly && authStore.isAuthenticated) {
    return authStore.role === 'medecin'
      ? { name: 'medecin-dashboard' }
      : { name: 'patient-dashboard' }
  }

  if (to.meta.role && authStore.role !== to.meta.role) {
    return authStore.role === 'medecin'
      ? { name: 'medecin-dashboard' }
      : { name: 'patient-dashboard' }
  }

  return true
})

export default router