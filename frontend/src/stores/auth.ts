import { defineStore } from 'pinia'
import api from '../services/api'

interface User {
  id: number
  name: string
  email: string
  role: 'patient' | 'medecin' | 'admin'
  [key: string]: any
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('mediconnect_user') || 'null') as User | null,
    token: localStorage.getItem('mediconnect_token') as string | null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    role: (state) => state.user?.role || null,
  },

  actions: {
    setSession(user: User, token: string) {
      this.user = user
      this.token = token
      localStorage.setItem('mediconnect_user', JSON.stringify(user))
      localStorage.setItem('mediconnect_token', token)
    },

    async login(email: string, password: string) {
      const response = await api.post('/login', { email, password })
      this.setSession(response.data.user, response.data.token)
      return response.data
    },

    async register(payload: Record<string, any>) {
      const response = await api.post('/register', payload)
      this.setSession(response.data.user, response.data.token)
      return response.data
    },

    async logout() {
      try {
        await api.post('/logout')
      } catch (e) {
        // deconnexion locale meme si l'appel echoue
      }
      this.user = null
      this.token = null
      localStorage.removeItem('mediconnect_user')
      localStorage.removeItem('mediconnect_token')
    },

    async fetchMe() {
      const response = await api.get('/me')
      this.user = response.data
      localStorage.setItem('mediconnect_user', JSON.stringify(response.data))
      return response.data
    },
  },
})