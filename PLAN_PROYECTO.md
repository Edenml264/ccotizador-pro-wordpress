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

- **Agregar más variables dinámicas** en los correos y plantillas (ej: {telefono}, {detalle_servicio}, etc).
- **Opción de editar la plantilla HTML** de cotización directamente desde el panel de administración para máxima personalización.
- **Exportar historial de cotizaciones** a Excel/CSV para análisis o respaldo.
- **Mejoras de seguridad:** Validar y sanitizar todos los datos, restringir el acceso a archivos y endpoints sensibles, usar nonces en AJAX.
- **Pruebas de envío de correos** y adjuntos para asegurar la correcta entrega y visualización en diferentes clientes de email.
- **Soporte multilingüe:** Añadir compatibilidad con plugins como WPML o Polylang si se requiere internacionalización.
- **Migrar el shortcode a bloque Gutenberg** para experiencia visual mejorada.
- **Personalización avanzada del PDF:** Logo, colores, datos de empresa editables desde el panel.
- **Agregar filtros y búsqueda** en el historial de cotizaciones.

## 7. Responsables
- Desarrollador: Eden Mendez
- Revisor: Eden Mendez

## 8. Recursos y Dependencias
- HTML, CSS, JavaScript
- Navegador web moderno
- Endpoint personalizado en WordPress para recepción y reenvío de cotizaciones por correo electrónico fijo.

## 7. Notas Adicionales
- Adaptar el plan según necesidades específicas del cliente.
- Considerar futuras mejoras como autenticación o base de datos.
