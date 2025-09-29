<template>
  <div>
    <v-data-table
      :items="filtered"
      :headers="headers"
      :loading="loading"
      class="elevation-1"
      item-key="id"                
    >
      <template #no-data>
        <div class="pa-6 text-center">No hay usuarios para mostrar.</div>
      </template>

      <template #item.acciones="{ item }">
        <div class="d-flex ga-2">
          <v-btn
            size="small"
            variant="tonal"
            color="primary"
            @click="openEdit(item)"
          >
            <v-icon size="18" class="mr-1">mdi-pencil</v-icon>
            Editar
          </v-btn>

          <v-btn
            size="small"
            variant="tonal"
            color="error"
            @click="openDelete(item)"
          >
            <v-icon size="18" class="mr-1">mdi-delete</v-icon>
            Eliminar
          </v-btn>
        </div>
      </template>
    </v-data-table>


    <v-dialog v-model="deleteDialog" max-width="460">
      <v-card>
        <v-card-title class="text-h6">
          ¿Eliminar usuario?
        </v-card-title>
        <v-card-text>
          Esta acción no se puede deshacer. ¿Seguro que deseas eliminar
          a <strong>{{ selectedUser?.nombre }}</strong>?
          <v-alert
            v-if="deleteError"
            type="error"
            class="mt-3"
            density="comfortable"
            variant="tonal"
          >
            {{ deleteError }}
          </v-alert>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="closeDelete" :disabled="deleteLoading">Cancelar</v-btn>
          <v-btn
            color="error"
            :loading="deleteLoading"
            @click="confirmDelete"
          >
            Eliminar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>


    <v-dialog v-model="editDialog" max-width="560">
      <v-card>
        <v-card-title class="text-h6">
          Editar usuario
        </v-card-title>
        <v-card-text>
          <v-form ref="editFormRef" @submit.prevent="submitEdit">
            <v-text-field
              v-model="editForm.nombre"
              label="Nombre"
              :rules="[v => !!v || 'El nombre es obligatorio']"
              class="mb-3"
            />
            <v-text-field
              v-model="editForm.email"
              label="Email"
              type="email"
              :rules="[v => !!v || 'El email es obligatorio']"
              class="mb-3"
            />
            <v-select
              v-model="editForm.rol"
              :items="roles"
              label="Rol"
              :rules="[v => !!v || 'El rol es obligatorio']"
              class="mb-3"
            />
            <v-text-field
              v-model="editForm.password"
              label="Contraseña (opcional)"
              type="password"
              hint="Déjala vacía si no deseas cambiarla"
              persistent-hint
              class="mb-1"
            />

            <v-alert
              v-if="editError"
              type="error"
              class="mt-3"
              density="comfortable"
              variant="tonal"
            >
              {{ editError }}
            </v-alert>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="closeEdit" :disabled="editLoading">Cancelar</v-btn>
          <v-btn
            color="primary"
            :loading="editLoading"
            @click="submitEdit"
          >
            Guardar cambios
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import api from '@/services/api'


type Usuario = { id:number; nombre:string; email:string; rol:'admin'|'usuario' }


type TableHeader =
  | { title: string; key: keyof Usuario; sortable?: boolean; align?: 'start'|'center'|'end' }
  | { title: string; key: 'acciones'; sortable?: boolean; align?: 'start'|'center'|'end' }


const props = defineProps<{ searchTerm?: string }>()

const items = ref<Usuario[]>([])
const loading = ref(false)


const headers: TableHeader[] = [
  { title: 'Nombre',  key: 'nombre' },
  { title: 'Email',   key: 'email' },
  { title: 'Rol',     key: 'rol' },
  { title: 'Acciones', key: 'acciones', sortable: false, align: 'end' },
]

// Carga desde API
const fetchUsers = async () => {
  loading.value = true
  try {
    const { data } = await api.get<Usuario[]>('/usuarios/listUsers')
    items.value = data
  } finally {
    loading.value = false
  }
}

onMounted(fetchUsers)


const filtered = computed(() => {
  const q = (props.searchTerm || '').toLowerCase().trim()
  if (!q) return items.value
  return items.value.filter(u =>
    u.nombre.toLowerCase().includes(q) ||
    u.email.toLowerCase().includes(q)  ||
    u.rol.toLowerCase().includes(q)
  )
})


const deleteDialog = ref(false)
const deleteLoading = ref(false)
const deleteError = ref('')
const selectedUser = ref<Usuario | null>(null)

function openDelete(user: Usuario) {
  selectedUser.value = user
  deleteError.value = ''
  deleteDialog.value = true
}

function closeDelete() {
  deleteDialog.value = false
  deleteLoading.value = false
  deleteError.value = ''
  selectedUser.value = null
}

async function confirmDelete() {
  if (!selectedUser.value) return
  deleteLoading.value = true
  deleteError.value = ''
  try {
    await api.delete(`/usuarios/deleteUser/${selectedUser.value.id}`)

   
    const removedId = selectedUser.value.id
    items.value = items.value.filter(u => u.id !== removedId)

  

    closeDelete()
  } catch (e: any) {
    deleteError.value = e?.response?.data?.message || e?.message || 'No se pudo eliminar'
    deleteLoading.value = false
  }
}


const editDialog = ref(false)
const editLoading = ref(false)
const editError = ref('')
const editFormRef = ref()

const roles = ['admin', 'usuario'] as const

const editForm = ref<{
  id: number | null
  nombre: string
  email: string
  rol: 'admin' | 'usuario' | null   
  password: string
}>({
  id: null,
  nombre: '',
  email: '',
  rol: null,                        
  password: '',
})

function openEdit(user: Usuario) {
  editError.value = ''
  editForm.value = {
    id: user.id,
    nombre: user.nombre,
    email: user.email,
    rol: user.rol,
    password: '',
  }
  editDialog.value = true
}

function closeEdit() {
  editDialog.value = false
  editLoading.value = false
  editError.value = ''
}

async function submitEdit() {
  if (!editForm.value.id) return
  editLoading.value = true
  editError.value = ''

  const payload: Record<string, unknown> = {
    nombre: editForm.value.nombre,
    email: editForm.value.email,
    rol: editForm.value.rol ?? undefined, 
  }
  if (editForm.value.password?.trim()) {
    payload.password = editForm.value.password
  }

  try {
    await api.put(`/usuarios/updateUser/${editForm.value.id}`, payload)

 
    const idx = items.value.findIndex(u => u.id === editForm.value.id)
    if (idx !== -1) {
      items.value[idx] = {
        ...items.value[idx],
        nombre: editForm.value.nombre,
        email: editForm.value.email,
        rol: (editForm.value.rol ?? items.value[idx].rol) as 'admin'|'usuario',
      }

      items.value = [...items.value]
    }

    closeEdit()
  } catch (e: any) {
    const res = e?.response
    if (res?.status === 422 || res?.status === 400) {
      const errorsObj = res?.data?.errors as Record<string, string[]> | undefined
      const firstMsg = errorsObj ? Object.values(errorsObj)[0]?.[0] : undefined
      editError.value = res?.data?.message || firstMsg || 'Error de validación'
    } else {
      editError.value = res?.data?.message || e?.message || 'No se pudo actualizar'
    }
    editLoading.value = false
  }
}
</script>
