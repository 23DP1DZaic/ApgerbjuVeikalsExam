import { API_URL, fetchWithAuth } from './api'

export type ListingImage = {
  id: number
  image_path: string
}

export type Listing = {
  id: number
  title: string
  description: string
  price: number
  category: string
  gender?: string | null
  brand?: string | null
  color?: string | null
  size?: string | null
  condition: string
  images: ListingImage[]
  user_id: number
}

export type AuthUser = {
  id: number
  name: string
  display_name?: string | null
  avatar?: string | null
  avatar_url?: string | null
  bio?: string | null
  email: string
  role?: string
  listings?: Listing[]
}

const USER_KEY = 'user'
const TOKEN_KEY = 'token'

export const getUser = (): AuthUser | null => {
  const rawUser = localStorage.getItem(USER_KEY)

  if (!rawUser) return null

  try {
    return JSON.parse(rawUser) as AuthUser
  } catch (error) {
    console.error('Failed to parse user from localStorage:', error)
    localStorage.removeItem(USER_KEY)
    return null
  }
}

export const getToken = (): string | null => {
  const token = localStorage.getItem(TOKEN_KEY)

  if (!token || token === 'undefined' || token === 'null') {
    return null
  }

  return token
}

export const isLoggedIn = (): boolean => !!getToken()

export const saveAuth = (user: AuthUser, token: string): void => {
  localStorage.setItem(USER_KEY, JSON.stringify(user))
  localStorage.setItem(TOKEN_KEY, token)
}

export const setUser = (user: AuthUser): void => {
  localStorage.setItem(USER_KEY, JSON.stringify(user))
}

export const clearAuth = (): void => {
  localStorage.removeItem(USER_KEY)
  localStorage.removeItem(TOKEN_KEY)
}

export const getCurrentUser = async (): Promise<AuthUser | null> => {
  const token = getToken()

  if (!token) return null

  try {
    const response = await fetchWithAuth(`${API_URL}/api/me`, {
      method: 'GET',
    })

    const rawText = await response.text()

    let data: any = null

    try {
      data = JSON.parse(rawText)
    } catch {
      data = null
    }

    if (!response.ok || !data) {
      clearAuth()
      return null
    }

    setUser(data)
    return data as AuthUser
  } catch (error) {
    console.error('Failed to fetch current user:', error)
    return null
  }
}