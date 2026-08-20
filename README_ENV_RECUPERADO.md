# ENV recuperado

Esta entrega incluye un archivo `.env` local restaurado a partir de la configuración de base de datos compartida previamente para Ixtlahuacán y de la configuración actual del App Service.

- `.env` contiene la configuración local/privada y está ignorado por Git.
- `.env.example` permanece sanitizado para que pueda subirse al repositorio.
- Para revisión de vistas se mantienen `AUTH_MODE=demo` y `DATA_MODE=demo`.
- Para activar posteriormente la base real, cambiar `DATA_MODE=db` y, cuando se valide autenticación, `AUTH_MODE=db`.

Importante: GitHub Actions no despliega archivos ignorados por Git. En Azure App Service, las variables privadas deben configurarse también como Application Settings / Variables de entorno.
