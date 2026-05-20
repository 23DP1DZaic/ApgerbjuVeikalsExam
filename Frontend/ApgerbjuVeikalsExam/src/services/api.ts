import { clearAuth, getToken } from './auth'

export const API_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000'

export const getAuthHeaders = (isFormData = false): HeadersInit => {
  const token = getToken()

  const headers: HeadersInit = {
    Accept: 'application/json',
  }

  if (!isFormData) {
    headers['Content-Type'] = 'application/json'
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
  const isFormData = init.body instanceof FormData

  const response = await fetch(input, {
    ...init,
    headers: {
      ...getAuthHeaders(isFormData),
      ...(init.headers || {}),
    },
  })

  if (response.status === 401) {
    clearAuth()
    window.dispatchEvent(new Event('auth-changed'))
  }

  return response
}