# Woo Min/Max Quantity Product

## Descripción

Este plugin permite establecer cantidades **mínimas y máximas** para productos **simples y variables** en WooCommerce. Incluye:

- Campos personalizados en productos simples y variaciones.
- Validaciones al añadir productos al carrito.
- Validaciones en el carrito y en el checkout.
- Inputs de cantidad personalizados que respetan los límites establecidos.
- Actualización dinámica de límites de cantidad en productos variables.
- Modal informativo en la pantalla de plugins.

---

## Instalación

1. Descarga o clona este repositorio.
2. Copia la carpeta del plugin en `wp-content/plugins/`.
3. Activa el plugin desde el panel de WordPress en **Plugins**.

---

## Uso

### Para productos simples

1. Edita un producto simple en WooCommerce.
2. En la pestaña **Inventario**, aparecerán los campos:
   - `Cantidad mínima`
   - `Cantidad máxima`
3. Ingresa los valores deseados y guarda los cambios.

### Para productos variables

1. Edita un producto variable.
2. En cada variación, aparecerán los campos:
   - `Cantidad mínima`
   - `Cantidad máxima`
3. Los límites se aplican automáticamente al seleccionar una variación en la tienda.

### Validaciones

- Al añadir al carrito, WooCommerce mostrará un error si la cantidad no cumple con los límites.
- En el carrito y checkout se aplican las mismas restricciones.
- Los inputs de cantidad se ajustan automáticamente según el mínimo y máximo definido.

---

## Personalización

- El plugin inyecta un modal informativo en la pantalla de plugins con detalles sobre el mismo.
- Se puede extender o modificar la lógica de validación según tus necesidades.
