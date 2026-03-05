# API REST Laravel 2FA

API REST con autenticación de dos factores (2FA), dispositivos de confianza y CRUD de servicios con soporte Base64.

## Tecnologías
- Laravel 11
- Laravel Sanctum (tokens)
- MySQL
- Mailtrap (envío de correos OTP)

## Requisitos previos
- PHP 8.2.12
- Composer
- MySQL
- XAMPP

## Instalación paso a paso

### 1. Clonar el repositorio
```bash
git clone <url-del-repositorio>
cd api-2fa
```

### 2. Instalar dependencias
```bash
composer install
```

### 3. Copiar archivo de entorno
```bash
cp .env.example .env
```

### 4. Generar clave de aplicación
```bash
php artisan key:generate
```

### 5. Configurar base de datos en .env
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=api_2fa
DB_USERNAME=root
DB_PASSWORD=
```

### 6. Configurar Mailtrap en .env
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=e00dd0c2ef0651
MAIL_PASSWORD=5a0a95a83a7ab0
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@api2fa.com"
MAIL_FROM_NAME="API 2FA"
```

### 7. Ejecutar migraciones y seeder
```bash
php artisan migrate:fresh --seed
```

### 8. Levantar el servidor
```bash
php artisan serve
```

El servidor estará disponible en: `http://127.0.0.1:8000`

## Credenciales de prueba
| Campo | Valor |
|---|---|
| Email | admin@test.com |
| Password | password123 |

## Endpoints disponibles

### Autenticación (públicos)
| Método | Endpoint | Descripción |

| POST | /api/auth/login | Login con email y password |
| POST | /api/auth/verify-otp | Verificar código OTP |

### Autenticación (protegidos)
| Método | Endpoint | Descripción |

| POST | /api/auth/logout | Cerrar sesión |
| GET | /api/auth/me | Ver usuario autenticado |

### Servicios (protegidos con Bearer Token)
| Método | Endpoint | Descripción |
|---|---|---|
| GET | /api/services | Listar servicios activos |
| POST | /api/services | Crear servicio con foto Base64 |
| GET | /api/services/{id} | Ver un servicio |
| PUT | /api/services/{id} | Actualizar servicio |
| DELETE | /api/services/{id} | Eliminar servicio (Soft Delete) |

## Flujo de autenticación 2FA
```
1. POST /api/auth/login
   → Credenciales correctas + dispositivo NO de confianza
   → Sistema envía OTP al correo (válido 10 minutos)

2. POST /api/auth/verify-otp
   → Validar OTP recibido por correo
   → Retorna Bearer Token para usar en requests protegidos
   → Con remember_device=true guarda el dispositivo como de confianza

3. Próximo login desde dispositivo de confianza:
   → Enviar header: X-Device-Token: {device_token}
   → Sistema omite el OTP y da el token directamente
```

## Ejemplo de uso con dispositivo de confianza

Agregar este header en el login:
```
X-Device-Token: uuid-del-dispositivo-guardado
```

## Validación de foto en Base64

El campo `foto_persona` acepta imágenes en formato Base64:
- Con prefijo: `data:image/png;base64,xxxxx`
- Sin prefijo: `xxxxx` (solo el string Base64)
- Formatos permitidos: jpeg, png, gif, webp
