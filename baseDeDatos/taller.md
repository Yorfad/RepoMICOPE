# 📘 Sistema de Gestión de Taller de Reparaciones

---

## 📋 Descripción General
Sistema completo para gestionar un taller de reparación de equipos electrónicos con capacidades avanzadas de **estimación**, **asignación inteligente** y **tracking**.

---

## 🎯 Características Principales

### 1. Gestión de Clientes
- Registro completo con origen del cliente (marketing).
- Contador automático de visitas.
- Historial de equipos y servicios.

### 2. Gestión de Técnicos
- Múltiples especialidades por técnico.
- Niveles de experiencia (Aprendiz, Bueno, Experto).
- Tracking de carga de trabajo.
- Asignación inteligente basada en carga y especialidad.

### 3. Gestión de Servicios
- Reportes preliminares del cliente.
- Diagnósticos técnicos profesionales.
- Múltiples técnicos por servicio (colaboración).
- Historial completo de estados con auditoría.
- Estados: `Recibido → Diagnosticando → Esperando repuestos → Reparando → Finalizado → Entregado`.

### 4. Estimaciones Inteligentes
- Cálculo automático de tiempos basado en:
  - Carga actual de técnicos.
  - Tiempos históricos por tipo de daño.
  - Disponibilidad de repuestos.
- Predicción de cuándo se verá y completará cada equipo.

### 5. Costos y Rentabilidad
- Separación de costos internos vs precios al cliente.
- Cálculo automático de ganancias.
- Control de repuestos con costo/precio.
- Mano de obra con costo/precio por hora.

### 6. Inventario y Pedidos
- Control de stock de repuestos.
- Sistema de pedidos con tracking.
- Actualización automática de inventario.
- Alertas de repuestos pendientes.

---

## 📊 Vistas Principales
- **vista_servicios_actuales** → Estado actual de cada servicio sin subconsultas pesadas.  
- **vista_costos_servicios** → Resumen financiero completo por servicio.  
- **vista_carga_tecnicos** → Carga de trabajo actual de cada técnico.  

---

## 🔧 Procedimientos Almacenados
- **registrar_servicio()** → Crea servicio y actualiza contador de visitas del cliente.  
- **asignar_tecnico_optimo()** → Asigna el mejor técnico según especialidad y carga de trabajo.  
- **estimar_tiempo_servicio()** → Calcula cuándo se verá y completará un nuevo equipo.  
- **equipos_cliente_en_servicio()** → Lista equipos activos de un cliente.  
- **dashboard_taller()** → Resumen ejecutivo del taller.  
- **cambiar_estado_servicio()** → Cambia estado con auditoría completa.  

---

## ⚙️ Triggers Automáticos
- Actualización automática de stock al usar repuestos.  
- Cálculo automático de costos/precios totales.  
- Cambio de estado cuando llegan repuestos pedidos.  

---

## 🎨 Flujo de Trabajo Típico
1. Cliente llega con equipo dañado.  
2. Se registra servicio con reporte preliminar.  
3. Sistema sugiere técnico óptimo y estima tiempos.  
4. Se asigna técnico automáticamente.  
5. Técnico diagnostica y cambia a **Reparando**.  
6. Se agregan repuestos y tiempos de mano de obra.  
7. Sistema calcula costos y ganancias automáticamente.  
8. Si falta repuesto → **Esperando repuestos** con tracking.  
9. Al finalizar → Cliente ve tiempo real vs estimado.  
10. Se entrega y queda en historial para futuras referencias.  

---

## 💡 Casos de Uso Avanzados
- Planificación de carga: ver qué técnico está menos ocupado.  
- Estimación al cliente: *"Tu equipo estará listo en 3-5 días"*.  
- Análisis de rentabilidad: qué servicios generan más ganancia.  
- Clientes VIP: identificar clientes recurrentes.  
- Análisis de daños: qué problemas son más comunes.  
- Pedidos pendientes: qué repuestos están en tránsito.  

---

## 📈 Reportes Disponibles
1. Equipos en reparación actualmente.  
2. Carga de trabajo por técnico.  
3. Servicios más rentables.  
4. Rendimiento de técnicos.  
5. Tipos de daños más comunes.  
6. Clientes con más visitas.  
7. Equipos con múltiples reparaciones.  
8. Servicios esperando repuestos.  
9. Dashboard ejecutivo completo.  

---

## 🔒 Integridad y Seguridad
- **Foreign keys** en todas las relaciones.  
- Índices optimizados para consultas frecuentes.  
- Auditoría completa de cambios de estado.  
- Restricciones de datos con ENUM.  
- Triggers para consistencia automática.  

---

## ⚡ Optimizaciones
- Vistas materializadas para consultas complejas.  
- Índices en campos de búsqueda frecuente.  
- Procedimientos para lógica de negocio compleja.  
- Evita subconsultas repetitivas.  

---

## 📝 Notas de Implementación
- Todos los precios en formato `DECIMAL(10,2)` para precisión.  
- Fechas con `DATETIME` para tracking preciso.  
- Estados controlados con `ENUM` para consistencia.  
- Soft delete disponible (`activo BOOLEAN`).  
- Extensible para agregar más tipos de equipos/daños.  

---

## 🖥️ Laravel (Fase 2)
Para implementar el CRUD en Laravel necesitarás:  
- **Models** para cada tabla principal.  
- **Controllers** con métodos `index`, `create`, `store`, `edit`, `update`, `destroy`.  
- **Views** con formularios y listados.  
- Relaciones Eloquent configuradas.  

---

## 🔢 Algoritmos Numéricos (Fase 3)
1. **Factorial** → Función recursiva simple.  
2. **Amortizaciones** → Cálculo financiero con bucle.  
3. **Binomio** → Triángulo de Pascal recursivo.  

---
