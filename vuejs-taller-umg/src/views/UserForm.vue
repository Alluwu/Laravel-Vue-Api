<template>
  <div class="page">
    <div class="card">
      <h2>Crear usuario</h2>

      <form @submit.prevent="onSubmit" class="form">
        <!-- Nombre -->
        <div class="field">
          <label>Nombre</label>
          <input v-model.trim="form.nombre" type="text" placeholder="Ej: Juan Pérez" />
          <p v-if="errors.nombre" class="error">{{ errors.nombre }}</p>
        </div>

        <!-- Email -->
        <div class="field">
          <label>Email</label>
          <input v-model.trim="form.email" type="email" placeholder="correo@ejemplo.com" />
          <p v-if="errors.email" class="error">{{ errors.email }}</p>
        </div>

        <!-- Password -->
        <div class="field">
          <label>Contraseña</label>
          <input v-model="form.password" type="password" placeholder="********" />
          <p v-if="errors.password" class="error">{{ errors.password }}</p>
        </div>

        <!-- Rol -->
        <div class="field">
          <label>Rol</label>
          <select v-model="form.rol">
            <option value="" disabled>Selecciona un rol</option>
            <option value="admin">Administrador</option>
            <option value="usuario">Usuario</option>
          </select>
          <p v-if="errors.rol" class="error">{{ errors.rol }}</p>
        </div>

        <!-- Botón -->
        <button type="submit" :disabled="loading" class="btn">
          {{ loading ? 'Guardando…' : 'Crear usuario' }}
        </button>

        <!-- Mensajes -->
      <p v-if="success" class="alert success">✅ Usuario creado correctamente</p>
      <p v-if="generalError" class="alert danger">⚠️ {{ generalError }}</p>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import api from '@/services/api'

type Form = {
  nombre: string
  email: string
  password: string
  rol: 'admin' | 'usuario' | ''
}

const form = reactive<Form>({
  nombre: '',
  email: '',
  password: '',
  rol: '',
})

const loading = ref(false)
const success = ref(false)
const generalError = ref('')
const errors = reactive<Record<string, string>>({})

function resetStatus() {
  success.value = false
  generalError.value = ''
  Object.keys(errors).forEach(k => delete errors[k])
}

async function onSubmit() {
  resetStatus()
  loading.value = true
  try {
    await api.post('/usuarios/addUser', form)
    success.value = true
    form.nombre = ''
    form.email = ''
    form.password = ''
    form.rol = ''
  } catch (e: any) {
    const res = e?.response
    if (res?.status === 422 || res?.status === 400) {
      const data = res.data
      if (data?.errors) {
        Object.keys(data.errors).forEach((field) => {
          errors[field] = Array.isArray(data.errors[field]) ? data.errors[field][0] : String(data.errors[field])
        })
      } else if (data?.message) {
        generalError.value = data.message
      } else {
        generalError.value = 'Error de validación'
      }
    } else {
      generalError.value = res?.data?.message || e?.message || 'Error al crear usuario'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
:root {
  --bg: #f6f7fb;
  --card: #ffffff;
  --text: #1f2937;
  --muted: #6b7280;
  --primary: #2563eb;
  --primary-hover: #1d4ed8;
  --border: #e5e7eb;
  --danger: #dc2626;
  --success: #16a34a;
  --radius: 16px;
}

.page {
  min-height: 100vh;
  display: grid;
  place-items: center;
  background: var(--bg);
  padding: 24px;
}

.card {
  width: 100%;
  max-width: 560px;
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  box-shadow: 0 10px 30px rgba(0,0,0,0.06);
  padding: 28px;
}

h2 {
  margin: 0 0 18px;
  font-size: 24px;
  font-weight: 800;
  color: var(--text);
  display: flex;
  align-items: center;
  gap: 10px;
}

.form {
  display: grid;
  gap: 14px;
}

.field {
  display: grid;
  gap: 6px;
}

label {
  font-size: 13px;
  font-weight: 600;
  color: var(--muted);
}

input,
select {
  width: 100%;
  font: inherit;
  color: var(--text);
  background: #fff;
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 10px 12px;
  outline: none;
  transition: box-shadow .2s, border-color .2s, transform .05s;
}

input::placeholder { color: #9ca3af; }

input:focus,
select:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, .15);
}

.btn {
  margin-top: 6px;
  width: 100%;
  border: none;
  border-radius: 10px;
  padding: 12px 14px;
  background-color: var(--primary);   
  color: #080808 !important;          
  font-weight: 700;
  font-size: 15px;
  letter-spacing: 0.3px;
  cursor: pointer;
  transition: background 0.2s, transform 0.05s, box-shadow 0.2s;
  box-shadow: 0 8px 16px rgba(37, 99, 235, 0.2);
}

.btn:hover {
  background-color: var(--primary-hover); 
}

.btn:active {
  transform: translateY(1px);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}


.error {
  color: var(--danger);
  font-size: 12px;
  margin-top: 2px;
}

.alert {
  margin-top: 10px;
  padding: 10px 12px;
  border-radius: 10px;
  font-size: 14px;
  border: 1px solid transparent;
}

.alert.success {
  color: var(--success);
  background: #e4d9d9;
  border-color: #a7f3d0;
}

.alert.danger {
  color: var(--danger);
  background: #fef2f2;
  border-color: #fecaca;
}

@media (max-width: 480px) {
  .card { padding: 20px; border-radius: 12px; }
  h2 { font-size: 20px; }
}
</style>
