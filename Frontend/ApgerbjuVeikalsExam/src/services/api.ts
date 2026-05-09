import { clearAuth, getToken } from './auth'

export const API_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000'

export const getAuthHeaders = (): HeadersInit => {
  const token = getToken()

  const headers: HeadersInit = {
    Accept: 'application/json',
  }

  if (token) {
    headers.Authorization = `Bearer ${token}`
  }

  return headers
}

export const fetchWithAuth = async (
  input: RequestInfo | URL,
  init: RequestInit = {}
): Promise<Response> => {
  const response = await fetch(input, {
    ...init,
    headers: {
      ...getAuthHeaders(),
      ...(init.headers || {}),
    },
  })

  if (response.status === 401) {
    clearAuth()
  }

  return response
}