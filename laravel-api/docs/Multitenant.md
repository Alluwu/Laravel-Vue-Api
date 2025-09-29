# 🌐 Arquitectura Multitenant

Este proyecto implementa **multi-tenant** basado en subdominios.

---

## 🔎 ¿Qué es multitenancy?
El **multitenancy** (multiarrendamiento) es un patrón de arquitectura en el que una sola aplicación sirve a **múltiples clientes (tenants)**.  
Cada tenant tiene su propio espacio de datos aislado, pero todos comparten la misma aplicación.

En este proyecto, cada tenant se identifica mediante un **subdominio** y su información se almacena en un **esquema separado en PostgreSQL**.

---

## 🏢 Modelo Tenant
Tabla `tenants`:
- **id**
- **name**
- **subdomain**
- **schema**

Cada `tenant` representa una empresa.  
Ejemplo:
```sql
INSERT INTO tenants (name, subdomain, schema)
VALUES ('Empresa 1', 'empresa1', 'empresa1');
