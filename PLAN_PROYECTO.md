# Plan de Proyecto: Cotizador

## 1. Estado Actual del Proyecto
El proyecto se ha migrado exitosamente a un **plugin de WordPress** que permite generar cotizaciones profesionales para servicios digitales o cualquier otro rubro. Actualmente incluye:
- Formulario web con campos personalizables para datos del cliente y servicios/productos.
- Cálculo automático del total de la cotización.
- Envío de la cotización como PDF adjunto al correo del administrador mediante integración con un endpoint de WordPress.
- Visualización profesional de la cotización y opción de impresión para el cliente.
- Estilos CSS modernos y responsivos para una experiencia visual atractiva.
- **Panel de administración** para personalizar los servicios, precios y etiquetas desde el backend de WordPress, sin necesidad de modificar código.

## 2. Objetivo de Evolución
El plugin está preparado para ser reutilizable y adaptable a distintos tipos de servicios y negocios, permitiendo la personalización total desde el panel de administración de WordPress.
## 2. Alcance
- Desarrollo de una interfaz web amigable para el usuario.
- Implementación de lógica para calcular cotizaciones.
- Posibilidad de exportar o imprimir la cotización generada.

## 3. Funcionalidades Principales
- Formulario para ingresar datos del cliente (nombre, correo electrónico y teléfono) y servicios/productos.
- Cálculo automático del total de la cotización.
- Envío de la cotización como PDF adjunto al correo del administrador (WordPress).
- Generación de una plantilla profesional de cotización/factura en HTML, con los datos del cliente y desglose de servicios/productos.
- Visualización de la cotización en una página aparte para el cliente tras enviar el formulario.
- Opción para imprimir la cotización desde la plantilla.
- Validación de datos ingresados.
- **Inputs y servicios totalmente personalizables desde el panel de administración.**
- Estilos visuales modernos y responsivos.
## 4. Mejoras y Personalización en el Plugin WordPress

- El formulario y la lógica de cotización están implementados como un **shortcode** fácil de insertar en cualquier página o entrada (visible para copiar desde el panel).
- Todos los servicios, precios y etiquetas pueden personalizarse desde el panel de administración.
- El diseño visual es profesional, atractivo y responsivo.
- El sistema es adaptable a nuevos servicios o cambios de precios sin editar código.
- **Cotizaciones guardadas realmente en base de datos y PDFs en el servidor.**
- **Historial de cotizaciones en el admin** con opción de descarga de cada PDF.
- **Notificaciones por email** al cliente y administrador, con asunto/cuerpo personalizables desde el panel.
- **Plantilla HTML profesional** (plantilla-cotizacion.html) enviada como cuerpo del correo.
- **Variables dinámicas** ({nombre}, {email}, {monto}, {archivo}, {fecha}) para personalizar los correos.

## 5. Cronograma Sugerido
| Fase                       | Descripción                               | Duración Estimada |
|----------------------------|-------------------------------------------|-------------------|
| Análisis y Diseño          | Definir requerimientos y diseño UI/UX      | 2 días            |
| Desarrollo Frontend        | Implementar interfaz y lógica de cálculo   | 3 días            |
| Integración como Plugin    | Modularizar y crear opciones en WP-Admin   | 3 días            |
| Pruebas y Validaciones     | Verificar funcionamiento y corregir errores| 2 días            |
| Documentación y Entrega    | Crear manual de usuario y entrega final    | 1 día             |

## 6. Recomendaciones y Siguientes Pasos

### 🟢 Prioridad Alta (Críticos / Mejoran experiencia y funcionalidad principal)

- ~~**Mostrar todos los datos relevantes en el historial de cotizaciones:** Fecha, cliente, email, monto y acciones (descargar PDF, ver detalles).~~ ✅
- ~~**Guardar la URL del PDF en la base de datos:** Permitir descarga directa desde el historial.~~ ✅ (ahora HTML)
- ~~**Mejoras de seguridad:** Validar y sanitizar todos los datos, restringir acceso a archivos y endpoints sensibles, usar nonces en AJAX y REST.~~ ✅
- ~~**Enviar todos los campos del formulario en el correo al admin.~~ ✅
- ~~**Permitir impresión/exportación de la cotización para el cliente.~~ ✅
- **Agregar filtros y búsqueda en el historial:** Por nombre, email, fecha, monto, etc.
- **Internacionalización:** Usar funciones de traducción para facilitar la localización del plugin.

### 🟡 Prioridad Media (Importantes / Mejoran administración y personalización)

- **Exportar historial de cotizaciones a Excel/CSV:** Útil para análisis y respaldo.
- **Personalización avanzada del PDF:** Logo, colores y datos de empresa editables desde el panel.
- **Opción de editar la plantilla HTML desde el panel:** Permitir máxima personalización sin tocar código.
- **Mejorar el diseño visual del historial:** Colores alternos, iconos, botones claros, paginación si hay muchas cotizaciones.

### 🟠 Prioridad Baja (Deseables / Futuras mejoras)

- **Agregar más variables dinámicas en correos y plantillas:** Ejemplo: {telefono}, {detalle_servicio}, etc.
- **Soporte multilingüe con WPML o Polylang.**
- **Migrar el shortcode a bloque Gutenberg:** Para experiencia visual mejorada en el editor de WordPress.
- **Pruebas avanzadas de envío de correos y adjuntos:** Asegurar compatibilidad con distintos clientes de email.

## 7. Responsables
- Desarrollador: Eden Mendez
- Revisor: Eden Mendez

## 8. Recursos y Dependencias
- HTML, CSS, JavaScript
- Navegador web moderno
- Endpoint personalizado en WordPress para recepción y reenvío de cotizaciones por correo electrónico fijo.

---

## 9. Instrucciones de Uso Detalladas

### Instalación
1. Descarga el plugin o clona el repositorio.
2. Sube la carpeta `cotizador` a `/wp-content/plugins/` en tu instalación de WordPress.
3. Activa el plugin desde el panel de administración de WordPress.

### Configuración y Personalización
- Ingresa al menú **Cotizador Pro** en el panel de administración.
- Personaliza los servicios, precios y etiquetas según tus necesidades.
- Configura los emails de notificación y la plantilla HTML si tu versión lo permite.
- Usa el shortcode `[cotizador_pro]` en cualquier página o entrada para mostrar el formulario de cotización.

### Uso para Clientes
- El cliente llena el formulario y recibe la cotización visualmente y por correo electrónico en PDF.
- Puede imprimir la cotización directamente desde la plantilla visual.

### Uso para Administradores
- Revisa el historial de cotizaciones en el panel de administración.
- Descarga los PDFs generados o visualiza los detalles de cada cotización.
- Exporta el historial a Excel/CSV si la funcionalidad está habilitada.

---

## 10. Recomendaciones para Publicación en WordPress.org

- **Cumple con las directrices oficiales:** Asegúrate de que tu código siga las [normas de plugins de WordPress](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/).
- **Incluye un archivo `readme.txt`** con formato estándar de WordPress, detallando descripción, instalación, FAQ, changelog y capturas de pantalla.
- **Internacionalización:** Usa funciones de traducción (`__()`, `_e()`) para permitir traducción a otros idiomas.
- **Licencia GPL:** El plugin debe estar bajo una licencia compatible con GPL.
- **Sin dependencias externas no permitidas:** No incluyas librerías que no sean open source o que tengan licencias restrictivas.
- **Pruebas:** Verifica el funcionamiento en las últimas versiones de WordPress y PHP.
- **Sube el plugin en un ZIP** y solicita la revisión en [https://wordpress.org/plugins/developers/add/](https://wordpress.org/plugins/developers/add/).
- **Responde a los revisores** y realiza los cambios que te sugieran para su aprobación.

Con estos pasos, tu plugin estará listo para ser compartido y utilizado por la comunidad global de WordPress.

## 7. Notas Adicionales
- Adaptar el plan según necesidades específicas del cliente.
- Considerar futuras mejoras como autenticación o base de datos.
