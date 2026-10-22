<template>
  <div class="registrador-app-container">
    <header class="top-bar">
      <p class="top-section-label">CELADOR - REGISTRO DE PARÁMETROS</p>
      <div class="top-title-row">
        <h1 class="top-title">Calidad de Agua & Aireación</h1>
        <button class="ghost-button" @click="$emit('lock-session')">
          <i class="fas fa-sign-out-alt me-1"></i>Cerrar sesión
        </button>
      </div>
      <div class="user-info">
        <span class="user-name">{{ user.name }}</span>
        <span class="user-role">{{ user.rol.toUpperCase() }}</span>
      </div>
      <div class="idle-bar-wrapper">
        <div class="idle-bar-bg">
          <div class="idle-bar-fg" :style="{ width: idlePercent + '%' }"></div>
        </div>
        <p class="idle-text">Tiempo restante: {{ idleRemainingSeconds }}s</p>
      </div>
    </header>

    <div class="wizard-steps">
      <div class="step" :class="{ active: step === 1 }" @click="goToStep(1)">
        <div class="step-number">1</div>
        <div class="step-label">Seleccionar Estanque</div>
      </div>
      <div class="step" :class="{ active: step === 2, disabled: !selectedSiembra }" @click="goToStep(2)">
        <div class="step-number">2</div>
        <div class="step-label">Registrar Datos</div>
      </div>
    </div>

    <div v-if="step === 1" class="step-content">
      <div class="step-header">
        <h2>Seleccionar Estanque</h2>
        <div class="step-info">
          <span class="badge">{{ totalSiembras }} siembras activas</span>
        </div>
      </div>

      <div class="filter-section">
        <div class="input-group">
          <span class="input-icon"><i class="fas fa-search"></i></span>
          <input 
            type="text" 
            class="text-input" 
            placeholder="Buscar estanque (ej. T7, E17, 81)..."
            v-model="filterText"
            @input="resetIdleTimer"
          />
        </div>
      </div>

      <div class="pond-grid">
        <div 
          v-for="siembra in filteredSiembras" 
          :key="siembra.id" 
          class="pond-card"
          @click="selectSiembra(siembra)"
        >
          <div class="pond-card-content">
            <div class="pond-number">{{ siembra.contenedor }}</div>
            <div class="pond-info">
              <div v-if="siembra.nombre_siembra" class="pond-name">
                {{ siembra.nombre_siembra }}
              </div>
              <div class="pond-action">
                <i class="fas fa-chevron-right"></i>
              </div>
            </div>
          </div>
        </div>

        <div v-if="!filteredSiembras.length && !siembrasLoading" class="empty-state">
          <i class="fas fa-water empty-icon"></i>
          <p>No se encontraron estanques</p>
          <small>Intenta con otro criterio de búsqueda</small>
        </div>
      </div>
    </div>

    <div v-else class="step-content">
      <button class="back-button" @click="backToPonds">
        <i class="fas fa-arrow-left"></i>
        <span>Volver a estanques</span>
      </button>

      <div class="selected-pond-info">
        <div class="pond-badge">
          <i class="fas fa-water"></i>
          <span>Estanque: <strong>{{ selectedSiembra.contenedor }}</strong></span>
        </div>
        <div v-if="selectedSiembra.nombre_siembra" class="pond-badge">
          <i class="fas fa-seedling"></i>
          <span>Siembra: <strong>{{ selectedSiembra.nombre_siembra }}</strong></span>
        </div>
      </div>

      <div class="modo-rapido-selector mb-4">
        <div class="modo-buttons">
          <button 
            class="modo-btn" 
            :class="{ active: modoRapido === 'agua' }"
            @click="modoRapido = 'agua'"
          >
            <i class="fas fa-tint"></i>
            <span>Solo Agua</span>
          </button>
          <button 
            class="modo-btn" 
            :class="{ active: modoRapido === 'combinado' }"
            @click="modoRapido = 'combinado'"
          >
            <i class="fas fa-tint"></i>
            <i class="fas fa-fan"></i>
            <span>Completo</span>
          </button>
          <button 
            class="modo-btn" 
            :class="{ active: modoRapido === 'aireacion' }"
            @click="modoRapido = 'aireacion'"
          >
            <i class="fas fa-fan"></i>
            <span>Solo Aireación</span>
          </button>
        </div>
      </div>

      <div v-if="modoRapido === 'agua'" class="form-container">
        <div class="form-header">
          <h3><i class="fas fa-tint"></i> Parámetros de Calidad de Agua</h3>
          <div class="date-display">
            <i class="fas fa-calendar"></i>
            {{ formatDate(form.fecha_parametro || today) }}
          </div>
        </div>

        <div class="form-card">
          <div class="form-section">
            <label class="section-label">
              <i class="fas fa-calendar-alt"></i>
              Fecha de Registro
            </label>
            <input 
              type="date" 
              class="form-input" 
              v-model="form.fecha_parametro" 
              @change="onFechaChange"
              required
              style="color-scheme: dark;"
            />
          </div>

          <div class="form-section">
            <label class="section-label">
              <i class="fas fa-thermometer-half"></i>
              Temperatura del Agua
            </label>
            <div class="input-with-unit">
              <input 
                type="number" 
                class="form-input" 
                v-model.number="form.temperatura" 
                step="0.01" 
                placeholder="0.00"
                @input="resetIdleTimer"
                style="color-scheme: dark;"
              />
              <span class="input-unit">°C</span>
            </div>
            <div class="input-hint">Hasta 2 decimales</div>
          </div>

          <div class="form-section">
            <label class="section-label">
              <i class="fas fa-wind"></i>
              Saturación de Oxígeno
            </label>
            <div class="input-hint" style="color: #cbd5e1; margin-bottom: 8px;">
              * Puede dejar horarios en blanco. Se requiere al menos una toma.
            </div>
            <div class="oxygen-grid">
              <div class="oxygen-time">
                <label>8:00 PM</label>
                <div class="input-with-unit">
                  <input 
                    type="number" 
                    class="form-input" 
                    v-model.number="form['8_pm']" 
                    step="0.01" 
                    placeholder="--"
                    @input="resetIdleTimer"
                    style="color-scheme: dark;"
                  />
                  <span class="input-unit">%</span>
                </div>
              </div>
              <div class="oxygen-time">
                <label>12:00 AM</label>
                <div class="input-with-unit">
                  <input 
                    type="number" 
                    class="form-input" 
                    v-model.number="form['12_am']" 
                    step="0.01" 
                    placeholder="--"
                    @input="resetIdleTimer"
                    style="color-scheme: dark;"
                  />
                  <span class="input-unit">%</span>
                </div>
              </div>
              <div class="oxygen-time">
                <label>4:00 AM</label>
                <div class="input-with-unit">
                  <input 
                    type="number" 
                    class="form-input" 
                    v-model.number="form['4_am']" 
                    step="0.01" 
                    placeholder="--"
                    @input="resetIdleTimer"
                    style="color-scheme: dark;"
                  />
                  <span class="input-unit">%</span>
                </div>
              </div>
            </div>
            <div class="input-hint">Hasta 2 decimales para cada medición</div>
          </div>

          <div class="form-actions">
            <button 
              class="primary-button" 
              @click="guardarSoloAgua"
              :disabled="saving"
            >
              <i v-if="saving" class="fas fa-spinner fa-spin"></i>
              <i v-else class="fas fa-save"></i>
              {{ saving ? 'Guardando...' : 'Guardar Parámetros' }}
            </button>
          </div>
        </div>
      </div>

      <div v-if="modoRapido === 'combinado'" class="form-container">
        <div class="combinado-header">
          <div class="combinado-title">
            <h3><i class="fas fa-tint"></i> + <i class="fas fa-fan"></i> Registro Completo</h3>
            <small class="text-success">
              <i class="fas fa-bolt"></i> Modo {{ modoAireacion === 'auto' ? 'automático' : 'manual' }} activado
            </small>
          </div>
          <div class="date-display">
            <i class="fas fa-calendar"></i>
            {{ formatDate(form.fecha_parametro || today) }}
          </div>
        </div>

        <div class="combinado-grid">
          <div class="combinado-col">
            <div class="combinado-card">
              <div class="card-header">
                <h4><i class="fas fa-tint text-primary"></i> Calidad del Agua</h4>
              </div>
              <div class="card-body">
                <div class="form-section-sm">
                  <label>Fecha registro</label>
                  <input 
                    type="date" 
                    class="form-input-sm" 
                    v-model="form.fecha_parametro"
                    @change="onFechaChange"
                    required
                    style="color-scheme: dark;"
                  />
                </div>

                <div class="form-section-sm">
                  <label>Temperatura (°C)</label>
                  <div class="input-with-unit-sm">
                    <input 
                      type="number" 
                      class="form-input-sm" 
                      v-model.number="form.temperatura" 
                      step="0.01" 
                      placeholder="0.00"
                      style="color-scheme: dark;"
                    />
                    <span class="input-unit-sm">°C</span>
                  </div>
                </div>

                <div class="form-section-sm">
                  <label class="section-label-sm">
                    <i class="fas fa-wind"></i>
                    Saturación de Oxígeno
                  </label>
                  <div class="input-hint-sm" style="color: #cbd5e1; margin-bottom: 5px;">
                    * Requiere mínimo 1 toma.
                  </div>
                  <div class="oxygen-grid-sm">
                    <div class="oxygen-time-sm">
                      <label>8:00 PM</label>
                      <div class="input-with-unit-sm">
                        <input 
                          type="number" 
                          class="form-input-sm" 
                          v-model.number="form['8_pm']" 
                          step="0.01" 
                          placeholder="--"
                          style="color-scheme: dark;"
                        />
                        <span class="input-unit-sm">%</span>
                      </div>
                    </div>
                    <div class="oxygen-time-sm">
                      <label>12:00 AM</label>
                      <div class="input-with-unit-sm">
                        <input 
                          type="number" 
                          class="form-input-sm" 
                          v-model.number="form['12_am']" 
                          step="0.01" 
                          placeholder="--"
                          style="color-scheme: dark;"
                        />
                        <span class="input-unit-sm">%</span>
                      </div>
                    </div>
                    <div class="oxygen-time-sm">
                      <label>4:00 AM</label>
                      <div class="input-with-unit-sm">
                        <input 
                          type="number" 
                          class="form-input-sm" 
                          v-model.number="form['4_am']" 
                          step="0.01" 
                          placeholder="--"
                          style="color-scheme: dark;"
                        />
                        <span class="input-unit-sm">%</span>
                      </div>
                    </div>
                  </div>
                  <div class="input-hint-sm mt-1">Hasta 2 decimales para cada medición</div>
                </div>
                </div>
            </div>
          </div>

          <div class="combinado-col">
            <div class="combinado-card aireacion-auto">
              <div class="card-header">
                <h4><i class="fas fa-fan text-success"></i> Aireación</h4>
                <div class="modo-selector">
                  <div class="btn-group btn-group-sm" role="group">
                    <button 
                      type="button" 
                      class="btn btn-outline-success" 
                      :class="{ active: modoAireacion === 'auto' }"
                      @click="modoAireacion = 'auto'"
                    >
                      <i class="fas fa-bolt"></i> Auto
                    </button>
                    <button 
                      type="button" 
                      class="btn btn-outline-info" 
                      :class="{ active: modoAireacion === 'manual' }"
                      @click="modoAireacion = 'manual'"
                    >
                      <i class="fas fa-calendar-alt"></i> Manual
                    </button>
                  </div>
                </div>
              </div>
              
              <div class="card-body">
                <div v-if="modoAireacion === 'auto' && configuracionesAireacion.length > 0" class="config-detected">
                  <div class="alert alert-success py-2 mb-3">
                    <div class="d-flex align-items-center">
                      <i class="fas fa-check-circle fa-lg mr-2"></i>
                      <div>
                        <strong>Configuraciones detectadas</strong><br>
                        <small>Seleccione los recursos a utilizar</small>
                      </div>
                    </div>
                    <div v-if="horaPredeterminadaInicio && horaPredeterminadaFin" class="mt-2 small text-muted">
                      <i class="fas fa-lightbulb"></i> Sugerencia: {{ horaPredeterminadaInicio }} - {{ horaPredeterminadaFin }}
                      ({{ configuracionActual?.minutos_hombre || '0' }} min)
                    </div>
                  </div>

                  <!-- =====================================================
                       AERADORES: cada uno con su horario integrado
                       ===================================================== -->
                  <div class="aeradores-container">

                    <p class="aeradores-hint mb-3">
                      <i class="fas fa-info-circle mr-1"></i>
                      Marque los aeradores que estuvieron encendidos e ingrese el horario de cada uno.
                    </p>

                    <div
                      v-for="(recurso, rIndex) in configuracionesAireacion"
                      :key="recurso.id"
                      class="aerador-card mb-3"
                      :class="{ 'aerador-activo': recurso.seleccionado }"
                    >
                      <!-- Cabecera: checkbox + nombre -->
                      <label :for="`rc-${recurso.id}`" class="aerador-cabecera mb-0">
                        <div class="d-flex align-items-center">
                          <input
                            type="checkbox"
                            class="aerador-chk mr-3"
                            :id="`rc-${recurso.id}`"
                            v-model="recurso.seleccionado"
                            @change="actualizarRecursosSeleccionados(recurso, rIndex)"
                          >
                          <div class="flex-grow-1">
                            <span class="aerador-nombre">
                              <i class="fas fa-fan mr-2" :class="recurso.seleccionado ? 'text-success' : 'text-muted'"></i>
                              {{ recurso.recurso.recurso }}
                            </span>
                            <small class="aerador-meta ml-2">{{ recurso.minutos_hombre }} min/hombre</small>
                          </div>
                          <span v-if="recurso.seleccionado" class="aerador-badge-activo">
                            <i class="fas fa-check mr-1"></i>Activo
                          </span>
                        </div>
                      </label>

                      <!-- Config expandida (visible al activar) -->
                      <div v-if="recurso.seleccionado" class="aerador-config">
                        <div v-for="(detalle, dIndex) in recursosDetalles" :key="dIndex">
                          <template v-if="detalle.recurso && detalle.recurso.id === recurso.id">

                            <!-- Inputs de tiempo -->
                            <div class="row aerador-tiempo-row mb-2">
                              <div class="col-6">
                                <label class="aerador-time-label inicio">
                                  <i class="fas fa-play-circle mr-1"></i> Hora Inicio
                                </label>
                                <input
                                  type="datetime-local"
                                  class="form-control form-control-sm input-dark"
                                  v-model="detalle.horaInicio"
                                  @change="calcularTiempoIndividual(dIndex)"
                                />
                              </div>
                              <div class="col-6">
                                <label class="aerador-time-label fin">
                                  <i class="fas fa-stop-circle mr-1"></i> Hora Fin
                                </label>
                                <input
                                  type="datetime-local"
                                  class="form-control form-control-sm input-dark"
                                  v-model="detalle.horaFin"
                                  @change="calcularTiempoIndividual(dIndex)"
                                />
                              </div>
                            </div>

                            <!-- Resultado o hint -->
                            <div v-if="detalle.tiempo" class="aerador-resultado mb-2">
                              <i class="fas fa-clock mr-1"></i>
                              <strong>{{ detalle.tiempo }} min</strong>
                              <span class="ml-2 small">
                                {{ formatFechaHora(detalle.horaInicio) }} → {{ formatFechaHora(detalle.horaFin) }}
                              </span>
                            </div>
                            <div v-else class="aerador-hint mb-2">
                              <i class="fas fa-arrow-up mr-1"></i>
                              Ingrese inicio y fin para calcular el tiempo de este aerador
                            </div>

                            <!-- Observaciones -->
                            <textarea
                              class="form-control form-control-sm input-dark aerador-obs"
                              v-model="detalle.detalles"
                              rows="2"
                              placeholder="Observaciones de este aerador (opcional)..."
                            ></textarea>

                          </template>
                        </div>
                      </div>
                    </div>

                    <!-- Botón agregar -->
                    <div class="text-center mb-3">
                      <button class="btn btn-outline-info btn-sm" @click="mostrarSelectorRecursos">
                        <i class="fas fa-plus mr-1"></i> Agregar otro aerador
                      </button>
                    </div>

                    <!-- Resumen (solo si hay al menos un tiempo calculado) -->
                    <div v-if="recursosDetalles.filter(d => d.recurso && d.tiempo).length > 0" class="aeradores-resumen mt-3">
                      <h6 class="resumen-title mb-2">
                        <i class="fas fa-list-check mr-2"></i>Resumen
                      </h6>
                      <div v-for="(detalle, index) in recursosDetalles" :key="index">
                        <div v-if="detalle.recurso && detalle.tiempo" class="resumen-fila">
                          <span class="resumen-nombre">
                            <i class="fas fa-fan mr-1 text-success"></i>
                            {{ detalle.recurso.recurso.recurso }}
                          </span>
                          <span class="resumen-tiempo">{{ detalle.tiempo }} min</span>
                        </div>
                      </div>
                      <div class="resumen-total">
                        <span>Total min/hombre</span>
                        <strong>{{ calcularTotalMinutosHombre().toFixed(2) }}</strong>
                      </div>
                    </div>

                  </div>
                </div>

                <div v-if="modoAireacion === 'manual'" class="modo-manual">
                  <div class="alert alert-info py-2 mb-3">
                    <div class="d-flex align-items-center">
                      <i class="fas fa-calculator fa-lg mr-2"></i>
                      <div>
                        <strong>Calculadora de Tiempo Manual</strong><br>
                        <small>Ingrese fecha y hora para calcular minutos de aireación (puede cruzar días)</small>
                      </div>
                    </div>
                  </div>

                  <div class="calculadora-manual mb-4">
                    <h6 class="mb-3">
                      <i class="fas fa-clock text-info mr-2"></i>
                      Duración de Aireación
                    </h6>
                    
                    <div class="row mb-3">
                      <div class="col-md-6">
                        <div class="form-group mb-2">
                          <label class="small">
                            <i class="fas fa-play-circle mr-1"></i> Fecha y Hora Inicio
                          </label>
                          <input
                            type="datetime-local"
                            class="form-control form-control-sm"
                            v-model="fechaHoraInicioManual"
                            @change="calcularTiempoManual"
                            placeholder="Seleccione fecha y hora"
                            style="color-scheme: dark;"
                          />
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group mb-2">
                          <label class="small">
                            <i class="fas fa-stop-circle mr-1"></i> Fecha y Hora Fin
                          </label>
                          <input
                            type="datetime-local"
                            class="form-control form-control-sm"
                            v-model="fechaHoraFinManual"
                            @change="calcularTiempoManual"
                            placeholder="Seleccione fecha y hora"
                            style="color-scheme: dark;"
                          />
                        </div>
                      </div>
                    </div>

                    <div v-if="tiempoManual" class="resultado-calculado text-center p-4 mb-4 border rounded bg-light">
                      <div class="display-4 text-primary mb-2">{{ tiempoManual }}</div>
                      <div class="small text-muted">minutos calculados</div>
                      <div v-if="diferenciaDiasManual > 0" class="small text-warning mb-2">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        Cruza {{ diferenciaDiasManual }} día(s)
                      </div>
                      <div class="mt-3">
                        <button 
                          class="btn btn-sm btn-outline-secondary" 
                          @click="aplicarCalculo"
                        >
                          <i class="fas fa-check mr-1"></i> Aplicar este cálculo
                        </button>
                      </div>
                    </div>
                  </div>

                  <div v-if="configuracionActual" class="configuracion-manual mb-4">
                    <div class="alert alert-success py-2">
                      <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle fa-lg mr-2"></i>
                        <div>
                          <strong>Configuración disponible</strong><br>
                          <small>{{ configuracionActual.recurso.recurso }} - {{ configuracionActual.minutos_hombre }} min/hombre</small>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div v-if="tiempoAplicado" class="resumen-guardar border-top pt-3">
                    <h6 class="mb-3">
                      <i class="fas fa-save text-info mr-2"></i>
                      Resumen para Guardar
                    </h6>
                    
                    <div class="resumen-grid">
                      <div class="resumen-item" v-if="fechaHoraInicioManual">
                        <small class="text-muted">Inicio</small>
                        <strong>{{ formatFechaHora(fechaHoraInicioManual) }}</strong>
                      </div>
                      <div class="resumen-item" v-if="fechaHoraFinManual">
                        <small class="text-muted">Fin</small>
                        <strong>{{ formatFechaHora(fechaHoraFinManual) }}</strong>
                      </div>
                      <div class="resumen-item" v-if="configuracionActual">
                        <small class="text-muted">Recurso</small>
                        <strong>{{ configuracionActual.recurso.recurso }}</strong>
                      </div>
                      <div class="resumen-item">
                        <small class="text-muted">Tiempo calculado</small>
                        <strong class="text-primary">{{ tiempoAplicado }} min</strong>
                      </div>
                      <div class="resumen-item" v-if="configuracionActual">
                        <small class="text-muted">Min/Hombre</small>
                        <strong class="text-success">{{ configuracionActual.minutos_hombre }} min</strong>
                      </div>
                      <div class="resumen-item" v-if="diferenciaDiasManual > 0">
                        <small class="text-muted">Días cruzados</small>
                        <strong class="text-warning">{{ diferenciaDiasManual }}</strong>
                      </div>
                    </div>
                  </div>
                </div>

                <div v-if="!configuracionesAireacion.length && modoAireacion === 'auto'" class="text-center py-4">
                  <i class="fas fa-fan fa-3x text-muted mb-3"></i>
                  <h6 class="text-muted">Sin configuración</h6>
                  <small class="d-block text-muted mb-3">
                    Esta siembra no tiene configuración de aireación
                  </small>
                  <button class="btn btn-outline-info btn-sm" @click="modoAireacion = 'manual'">
                    <i class="fas fa-calculator mr-1"></i> Usar calculadora manual
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="combinado-actions mt-4">
          <button
            class="primary-button large"
            @click="guardarCombinado"
            :disabled="saving ||
                      (modoAireacion === 'auto' && recursosDetalles.filter(d => d.recurso && d.tiempo).length === 0) ||
                      (modoAireacion === 'manual' && !tiempoAplicado) ||
                      !aguaCompleta"
          >
            <i v-if="saving" class="fas fa-spinner fa-spin"></i>
            <i v-else class="fas fa-save"></i>
            {{ saving ? 'Guardando...' : 'Guardar Todo' }}
            <small class="ml-2">(Agua + Aireación)</small>
          </button>
          <div v-if="!aguaCompleta" class="alert alert-warning mt-2 py-2">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Complete todos los campos obligatorios de agua (fecha, temperatura y al menos un oxígeno) para guardar
          </div>
        </div>
      </div>


      <div v-if="modoRapido === 'aireacion'" class="form-container">
        <div class="form-header">
          <h3><i class="fas fa-fan"></i> Registro Rápido de Aireación</h3>
          <div class="date-display">
            <i class="fas fa-calendar"></i>
            {{ formatDate(today) }}
          </div>
        </div>

        <div class="form-card">
          <div class="modo-selector-container mb-4">
            <div class="btn-group d-flex" role="group">
              <button 
                type="button" 
                class="btn btn-sm flex-fill" 
                :class="modoAireacion === 'auto' ? 'btn-success' : 'btn-outline-success'"
                @click="modoAireacion = 'auto'"
              >
                <i class="fas fa-bolt"></i> Automático
              </button>
              <button 
                type="button" 
                class="btn btn-sm flex-fill" 
                :class="modoAireacion === 'manual' ? 'btn-info' : 'btn-outline-info'"
                @click="modoAireacion = 'manual'; resetCalculadoraManual()"
              >
                <i class="fas fa-calculator"></i> Calculadora Manual
              </button>
            </div>
          </div>

          <div v-if="modoAireacion === 'auto' && configuracionesAireacion.length > 0">
            <div class="alert alert-success">
              <div class="d-flex align-items-start">
                <i class="fas fa-check-circle fa-lg mt-1 mr-3"></i>
                <div>
                  <h6 class="alert-heading mb-2">Configuración detectada</h6>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="mb-2">
                        <small class="text-muted">Recursos disponibles</small>
                        <div class="font-weight-bold">{{ configuracionesAireacion.length }} recurso(s)</div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="mb-2">
                        <small class="text-muted">Primer recurso</small>
                        <div class="font-weight-bold text-primary">{{ configuracionesAireacion[0]?.recurso?.recurso || 'N/A' }}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <p class="aeradores-hint mb-3">
              <i class="fas fa-info-circle mr-1"></i>
              Marque los aeradores que estuvieron encendidos e ingrese el horario de cada uno.
            </p>

            <div
              v-for="(recurso, rIndex) in configuracionesAireacion"
              :key="recurso.id"
              class="aerador-card mb-3"
              :class="{ 'aerador-activo': recurso.seleccionado }"
            >
              <label :for="`rs-${recurso.id}`" class="aerador-cabecera mb-0">
                <div class="d-flex align-items-center">
                  <input
                    type="checkbox"
                    class="aerador-chk mr-3"
                    :id="`rs-${recurso.id}`"
                    v-model="recurso.seleccionado"
                    @change="actualizarRecursosSeleccionados(recurso, rIndex)"
                  >
                  <div class="flex-grow-1">
                    <span class="aerador-nombre">
                      <i class="fas fa-fan mr-2" :class="recurso.seleccionado ? 'text-success' : 'text-muted'"></i>
                      {{ recurso.recurso.recurso }}
                    </span>
                    <small class="aerador-meta ml-2">{{ recurso.minutos_hombre }} min/hombre</small>
                  </div>
                  <span v-if="recurso.seleccionado" class="aerador-badge-activo">
                    <i class="fas fa-check mr-1"></i>Activo
                  </span>
                </div>
              </label>

              <div v-if="recurso.seleccionado" class="aerador-config">
                <div v-for="(detalle, dIndex) in recursosDetalles" :key="dIndex">
                  <template v-if="detalle.recurso && detalle.recurso.id === recurso.id">

                    <div class="row aerador-tiempo-row mb-2">
                      <div class="col-6">
                        <label class="aerador-time-label inicio">
                          <i class="fas fa-play-circle mr-1"></i> Hora Inicio
                        </label>
                        <input
                          type="datetime-local"
                          class="form-control form-control-sm input-dark"
                          v-model="detalle.horaInicio"
                          @change="calcularTiempoIndividual(dIndex)"
                        />
                      </div>
                      <div class="col-6">
                        <label class="aerador-time-label fin">
                          <i class="fas fa-stop-circle mr-1"></i> Hora Fin
                        </label>
                        <input
                          type="datetime-local"
                          class="form-control form-control-sm input-dark"
                          v-model="detalle.horaFin"
                          @change="calcularTiempoIndividual(dIndex)"
                        />
                      </div>
                    </div>

                    <div v-if="detalle.tiempo" class="aerador-resultado mb-2">
                      <i class="fas fa-clock mr-1"></i>
                      <strong>{{ detalle.tiempo }} min</strong>
                      <span class="ml-2 small">
                        {{ formatFechaHora(detalle.horaInicio) }} → {{ formatFechaHora(detalle.horaFin) }}
                      </span>
                    </div>
                    <div v-else class="aerador-hint mb-2">
                      <i class="fas fa-arrow-up mr-1"></i>
                      Ingrese inicio y fin para calcular el tiempo de este aerador
                    </div>

                    <textarea
                      class="form-control form-control-sm input-dark aerador-obs"
                      v-model="detalle.detalles"
                      rows="2"
                      placeholder="Observaciones de este aerador (opcional)..."
                    ></textarea>

                  </template>
                </div>
              </div>
            </div>

            <!-- Resumen cuando hay al menos un tiempo calculado -->
            <div v-if="recursosDetalles.filter(d => d.recurso && d.tiempo).length > 0" class="aeradores-resumen mt-3">
              <h6 class="resumen-title mb-2">
                <i class="fas fa-list-check mr-2"></i>Resumen
              </h6>
              <div v-for="(detalle, index) in recursosDetalles" :key="index">
                <div v-if="detalle.recurso && detalle.tiempo" class="resumen-fila">
                  <span class="resumen-nombre">
                    <i class="fas fa-fan mr-1 text-success"></i>
                    {{ detalle.recurso.recurso.recurso }}
                  </span>
                  <span class="resumen-tiempo">{{ detalle.tiempo }} min</span>
                </div>
              </div>
              <div class="resumen-total">
                <span>Total min/hombre</span>
                <strong>{{ calcularTotalMinutosHombre().toFixed(2) }}</strong>
              </div>
            </div>

            <div class="form-actions">
              <button
                class="primary-button"
                @click="guardarSoloAireacion"
                :disabled="saving || recursosDetalles.filter(d => d.recurso && d.tiempo).length === 0"
              >
                <i v-if="saving" class="fas fa-spinner fa-spin"></i>
                <i v-else class="fas fa-fan"></i>
                {{ saving ? 'Guardando...' : 'Guardar Aireación' }}
              </button>

              <button class="secondary-button" @click="modoRapido = 'combinado'">
                <i class="fas fa-arrow-left"></i>
                Volver a completo
              </button>
            </div>
          </div>

          <div v-if="modoAireacion === 'manual'">
            <div class="alert alert-info">
              <div class="d-flex align-items-start">
                <i class="fas fa-calculator fa-lg mt-1 mr-3"></i>
                <div>
                  <h6 class="alert-heading mb-2">Calculadora de Tiempo Manual</h6>
                  <small>Ingrese fecha y hora para calcular minutos de aireación (puede cruzar días)</small>
                </div>
              </div>
            </div>

            <div class="calculadora-completa mb-4">
              <div class="calculadora-container border rounded p-4 mb-4 bg-light">
                <h6 class="mb-3 text-center">
                  <i class="fas fa-clock text-info mr-2"></i>
                  Calcular Duración
                </h6>
                
                <div class="row">
                  <div class="col-md-5">
                    <div class="form-group">
                      <label class="small">Fecha y Hora Inicio</label>
                      <input
                        type="datetime-local"
                        class="form-control form-control-lg text-center"
                        v-model="fechaHoraInicioManual"
                        @change="calcularTiempoManual"
                        placeholder="Seleccione fecha y hora"
                        style="color-scheme: dark;"
                      />
                    </div>
                  </div>
                  
                  <div class="col-md-2 d-flex align-items-center justify-content-center">
                    <div class="text-muted">
                      <i class="fas fa-arrow-right fa-2x"></i>
                    </div>
                  </div>
                  
                  <div class="col-md-5">
                    <div class="form-group">
                      <label class="small">Fecha y Hora Fin</label>
                      <input
                        type="datetime-local"
                        class="form-control form-control-lg text-center"
                        v-model="fechaHoraFinManual"
                        @change="calcularTiempoManual"
                        placeholder="Seleccione fecha/hora"
                        style="color-scheme: dark;"
                      />
                    </div>
                  </div>
                </div>

                <div v-if="tiempoManual" class="resultado-calculado mt-4 p-4 border rounded bg-white text-center">
                  <div class="display-2 text-primary mb-3">{{ tiempoManual }}</div>
                  <div class="h5 text-muted mb-3">minutos calculados</div>
                  <div v-if="diferenciaDiasManual > 0" class="h6 text-warning mb-3">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Cruza {{ diferenciaDiasManual }} día(s)
                  </div>
                  <div class="d-flex justify-content-center gap-2">
                    <button 
                      class="btn btn-success" 
                      @click="aplicarCalculo"
                    >
                      <i class="fas fa-check mr-2"></i> Aplicar cálculo
                    </button>
                    <button 
                      class="btn btn-outline-secondary" 
                      @click="resetCalculadoraManual"
                    >
                      <i class="fas fa-redo mr-2"></i> Reiniciar
                    </button>
                  </div>
                </div>
              </div>

              <div v-if="configuracionActual" class="configuracion-disponible mb-4">
                <div class="alert alert-success">
                  <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle fa-lg mr-2"></i>
                    <div>
                      <strong>Configuración disponible para aplicar</strong><br>
                      <small>{{ configuracionActual.recurso.recurso }} - {{ configuracionActual.minutos_hombre }} min/hombre</small>
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="tiempoAplicado" class="resumen-guardar border-top pt-4 mt-4">
                <h5 class="mb-4 text-center">
                  <i class="fas fa-file-alt text-info mr-2"></i>
                  Resumen para Guardar
                </h5>
                
                <div class="resumen-detallado p-4 border rounded bg-light">
                  <div class="row">
                    <div class="col-md-6 mb-3" v-if="fechaHoraInicioManual">
                      <div class="text-center">
                        <small class="text-muted d-block">Inicio</small>
                        <strong class="h5">{{ formatFechaHora(fechaHoraInicioManual) }}</strong>
                      </div>
                    </div>
                    <div class="col-md-6 mb-3" v-if="configuracionActual">
                      <div class="text-center">
                        <small class="text-muted d-block">Recurso</small>
                        <strong class="h5">{{ configuracionActual.recurso.recurso }}</strong>
                      </div>
                    </div>
                  </div>
                  
                  <div class="row mb-4">
                    <div class="col-md-4 mb-3" v-if="fechaHoraInicioManual">
                      <div class="text-center">
                        <small class="text-muted d-block">Hora inicio</small>
                        <strong class="h4">{{ getHoraFromDateTime(fechaHoraInicioManual) }}</strong>
                      </div>
                    </div>
                    <div class="col-md-4 mb-3">
                      <div class="text-center">
                        <small class="text-muted d-block">Tiempo total</small>
                        <strong class="h1 text-primary">{{ tiempoAplicado }}</strong>
                        <div class="small text-muted">minutos</div>
                      </div>
                    </div>
                    <div class="col-md-4 mb-3" v-if="fechaHoraFinManual">
                      <div class="text-center">
                        <small class="text-muted d-block">Hora fin</small>
                        <strong class="h4">{{ getHoraFromDateTime(fechaHoraFinManual) }}</strong>
                      </div>
                    </div>
                  </div>
                  
                  <div v-if="configuracionActual" class="text-center">
                    <small class="text-muted d-block">Minutos/Hombre aplicados</small>
                    <strong class="h4 text-success">{{ configuracionActual.minutos_hombre }} min</strong>
                  </div>
                  
                  <div v-if="diferenciaDiasManual > 0" class="text-center mt-3">
                    <span class="badge badge-warning">
                      <i class="fas fa-calendar-alt mr-1"></i>
                      Cruza {{ diferenciaDiasManual }} día(s)
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-actions" v-if="tiempoAplicado">
              <button 
                class="primary-button"
                @click="guardarSoloAireacionManual"
                :disabled="saving"
              >
                <i v-if="saving" class="fas fa-spinner fa-spin"></i>
                <i v-else class="fas fa-save"></i>
                {{ saving ? 'Guardando...' : 'Guardar Aireación Calculada' }}
              </button>
              
              <button class="secondary-button" @click="resetCalculadoraManual">
                <i class="fas fa-redo"></i>
                Nueva Cálculo
              </button>
            </div>
          </div>

          <div v-if="modoAireacion === 'auto' && !configuracionesAireacion.length" class="text-center py-5">
            <i class="fas fa-fan fa-4x text-muted mb-4"></i>
            <h5 class="text-muted mb-3">No hay configuración automática</h5>
            <p class="text-muted mb-4">
              Esta siembra no tiene configuración de aireación predefinida.
            </p>
            <button class="btn btn-outline-info mb-2" @click="modoAireacion = 'manual'; resetCalculadoraManual()">
              <i class="fas fa-calculator mr-2"></i>
              Usar calculadora manual
            </button>
            <br>
            <button class="btn btn-primary" @click="modoRapido = 'combinado'">
              <i class="fas fa-arrow-left mr-2"></i>
              Volver a registro completo
            </button>
          </div>
        </div>
      </div>

      <div v-if="currentTab === 'history'" class="history-container">
        <div class="history-header">
          <h3><i class="fas fa-history"></i> Historial de Registros</h3>
          <div class="history-tabs">
            <button 
              class="history-tab" 
              :class="{ active: historialTipo === 'parameters' }"
              @click="historialTipo = 'parameters'; cargarHistorial()"
            >
              Parámetros
            </button>
            <button 
              class="history-tab" 
              :class="{ active: historialTipo === 'aireacion' }"
              @click="historialTipo = 'aireacion'; cargarHistorialAireacion()"
            >
              Aireación
            </button>
          </div>
        </div>

        <div class="history-content">
          <div v-if="historialLoading" class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Cargando historial...</p>
          </div>

          <div v-else>
            <div v-if="historialTipo === 'parameters' && historial.length">
              <div class="history-list">
                <div 
                  v-for="log in historial" 
                  :key="log.id" 
                  class="history-item"
                  @click="cargarRegistroEnFormulario(log)"
                >
                  <div class="history-item-header">
                    <div class="history-date">
                      <i class="fas fa-calendar"></i>
                      {{ formatDate(log.fecha_parametro) }}
                    </div>
                    <span class="history-action">Tocar para editar</span>
                  </div>
                  <div class="history-data-grid">
                    <div class="data-item">
                      <small>Temperatura</small>
                      <strong>{{ log.temperatura || '-' }}°C</strong>
                    </div>
                    <div class="data-item">
                      <small>8:00 PM</small>
                      <strong>{{ log['8_pm'] || '-' }}%</strong>
                    </div>
                    <div class="data-item">
                      <small>12:00 AM</small>
                      <strong>{{ log['12_am'] || '-' }}%</strong>
                    </div>
                    <div class="data-item">
                      <small>4:00 AM</small>
                      <strong>{{ log['4_am'] || '-' }}%</strong>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="historialTipo === 'aireacion' && historialAireacion.length">
              <div class="history-list">
                <div 
                  v-for="aireacion in historialAireacion" 
                  :key="aireacion.id" 
                  class="history-item aeration-item"
                >
                  <div class="history-item-header">
                    <div class="history-title">
                      <i class="fas fa-fan"></i>
                      Aireación Mecánica
                    </div>
                    <div class="history-date">
                      {{ formatDate(aireacion.fecha) }}
                    </div>
                  </div>
                  <div class="history-data-grid">
                    <div class="data-item">
                      <small>Recurso</small>
                      <strong>{{ aireacion.recurso_nombre || aireacion.recurso?.recurso || '-' }}</strong>
                    </div>
                    <div class="data-item">
                      <small>Tiempo</small>
                      <strong>{{ aireacion.minutos_calculados || aireacion.cantidad_recurso || 0 }} min</strong>
                    </div>
                    <div class="data-item">
                      <small>Min/Hombre</small>
                      <strong>{{ aireacion.minutos_hombre || 0 }} min</strong>
                    </div>
                  </div>
                  <div v-if="aireacion.detalles" class="history-observation">
                    <small>{{ aireacion.detalles }}</small>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="(!historial.length && historialTipo === 'parameters') || 
                       (!historialAireacion.length && historialTipo === 'aireacion')" 
                 class="empty-history">
              <i class="fas fa-clipboard-list"></i>
              <p>No hay registros para esta fecha</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showSuccessModal" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <h3 class="modal-title">{{ successTitle }}</h3>
        <p class="modal-message">{{ successMessage }}</p>
        <button class="modal-button" @click="closeSuccessModal">
          Aceptar
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';
import { filterPonds } from './pondFilter';

export default {
  name: "CeladorApp",
  props: {
    user: { type: Object, required: true }
  },
  
  data() {
    return {
      // Navigation
      step: 1,
      currentTab: 'parameters',
      modoRapido: 'combinado', // 'agua', 'combinado', 'aireacion'
      modoAireacion: 'auto', // 'auto', 'manual'
      
      // Data
      siembras: [],
      siembrasLoading: false,
      filterText: "",
      selectedSiembra: null,
      
      // Parameters form - MODIFICADO: Cambiado "7_am" por "4_am"
      form: {
        fecha_parametro: "",
        temperatura: "",
        "12_am": "",
        "4_am": "", // CAMBIO: "7_am" → "4_am"
        "8_pm": "",
      },
      saving: false,
      today: "",
      
      // Aeration automation - AHORA VACÍOS POR DEFECTO
      configuracionActual: null,
      configuracionesAireacion: [],
      fechaHoraInicioAuto: "",
      fechaHoraFinAuto: "",
      tiempoCalculado: null,
      diferenciaDias: 0,
      horaPredeterminadaInicio: "07:00",
      horaPredeterminadaFin: "07:15",
      
      // Para selección múltiple de recursos
      recursosSeleccionados: [],
      recursoActivoIndex: 0,
      recursosDetalles: [
        {
          recurso: null,
          tiempo: null,
          horaInicio: "",
          horaFin: "",
          minutosHombre: "0.00",
          detalles: ""
        }
      ],
      
      // Manual calculator - AHORA VACÍOS POR DEFECTO
      fechaHoraInicioManual: "",
      fechaHoraFinManual: "",
      tiempoManual: null,
      tiempoAplicado: null,
      diferenciaDiasManual: 0,
      
      // History
      historial: [],
      historialAireacion: [],
      historialLoading: false,
      historialTipo: "parameters",
      
      // Success modal
      successTitle: "",
      successMessage: "",
      showSuccessModal: false,
      
      // Inactivity
      idleSeconds: 0,
      idleMaxSeconds: 120,
      idleInterval: null,
    };
  },

  computed: {
    totalSiembras() { return this.siembras.length; },

    filteredSiembras() {
      if (!this.filterText) return this.siembras;
      return filterPonds(this.siembras, this.filterText);
    },

    idlePercent() {
      const pct = Math.max(0, 100 - Math.round((this.idleSeconds / this.idleMaxSeconds) * 100));
      return isNaN(pct) ? 100 : pct;
    },

    idleRemainingSeconds() {
      const remain = this.idleMaxSeconds - this.idleSeconds;
      return remain > 0 ? remain : 0;
    },

    // CORRECCIÓN: Validación de datos de agua (Permite dejar en blanco, requiere al menos 1 oxígeno)
    aguaCompleta() {
      const hasValue = (val) => val !== '' && val !== null && val !== undefined;

      const datosBasicos = this.form.fecha_parametro && 
                           this.form.fecha_parametro.trim() !== '' &&
                           hasValue(this.form.temperatura);

      const algunOxigeno = hasValue(this.form['8_pm']) || 
                           hasValue(this.form['12_am']) || 
                           hasValue(this.form['4_am']);

      return datosBasicos && algunOxigeno;
    },
  },

  watch: {
    'selectedSiembra.id': {
      handler(siembraId) {
        if (siembraId) {
          this.cargarConfiguracionAuto(siembraId);
        }
      }
    }
  },

  methods: {
    // Navigation methods
    goToStep(stepNumber) {
      if (stepNumber === 2 && !this.selectedSiembra) return;
      this.step = stepNumber;
      this.resetIdleTimer();
    },

    backToPonds() {
      this.step = 1;
      this.selectedSiembra = null;
      this.resetForm();
      this.modoRapido = 'combinado';
      this.modoAireacion = 'auto';
      this.historial = [];
      this.limpiarTimerInactividad();
    },

    switchToAeration() {
      this.currentTab = 'aeration';
      this.modoRapido = 'aireacion';
      this.resetIdleTimer();
    },

    switchToHistory() {
      this.currentTab = 'history';
      this.cargarHistorial();
    },

    // Inactivity timer
    iniciarTimerInactividad() {
      this.limpiarTimerInactividad();
      this.idleSeconds = 0;

      window.addEventListener("mousemove", this.resetIdleTimer);
      window.addEventListener("keydown", this.resetIdleTimer);
      window.addEventListener("touchstart", this.resetIdleTimer);

      this.idleInterval = setInterval(() => {
        this.idleSeconds++;
        if (this.idleSeconds >= this.idleMaxSeconds) {
          this.$emit('lock-session');
        }
      }, 1000);
    },

    limpiarTimerInactividad() {
      if (this.idleInterval) {
        clearInterval(this.idleInterval);
        this.idleInterval = null;
      }
      window.removeEventListener("mousemove", this.resetIdleTimer);
      window.removeEventListener("keydown", this.resetIdleTimer);
      window.removeEventListener("touchstart", this.resetIdleTimer);
    },

    resetIdleTimer() { this.idleSeconds = 0; },

    // Data loading
    async cargarSiembras() {
      this.siembrasLoading = true;
      try {
        const { data } = await axios.get("api/siembras", {
          params: {
            estado_siembra: 1,
            id_siembra: -1,
            contenedor_id: -1,
            phase: -1,
            page: 1,
            type: "",
            nro_results: 200,
          },
        });

        this.siembras = data.siembra?.data || data.data || [];
      } catch (e) {
        this.siembras = [];
      } finally {
        this.siembrasLoading = false;
      }
    },

    async selectSiembra(siembra) {
      this.selectedSiembra = siembra;
      this.step = 2;
      this.currentTab = 'parameters';
      this.modoRapido = 'combinado';
      this.modoAireacion = 'auto';
      this.resetForm();
      this.resetCalculadoraManual();
      this.iniciarTimerInactividad();
      
      if (siembra.id) {
        await this.cargarConfiguracionAuto(siembra.id);
      }
    },

    // Parameters form
    resetForm() {
      this.setToday();
      this.form = {
        fecha_parametro: "", // AHORA VACÍO POR DEFECTO
        temperatura: "",
        "12_am": "",
        "4_am": "", 
        "8_pm": "",
      };
      
      this.resetCalculadoraManual();
    },

    onFechaChange() {
      this.resetIdleTimer();
      this.cargarHistorial();
      this.buscarRegistroExistente();
    },

    // CORRECCIÓN: Renombrado a validarAguaCompleta (que era el que llamabas en tu guardado)
    validarAguaCompleta() {
      if (!this.aguaCompleta) {
        this.mostrarAlerta("warning", "Datos incompletos", "Debe ingresar fecha, temperatura y al menos un registro de oxígeno.");
        return false;
      }
      return true;
    },

    // ========== AUTOMATION METHODS ==========
    
    async cargarConfiguracionAuto(siembraId) {
      try {
        const { data } = await axios.get("api/siembra-recurso-datos", {
          params: { siembra_id: siembraId }
        });
        
        if (Array.isArray(data) && data.length > 0) {
          this.configuracionesAireacion = data.map((config, index) => ({
            ...config,
            seleccionado: index === 0,
            tiempo: null,
            horaInicio: "",
            horaFin: "",
            detalles: config.detalles || ""
          }));
          
          this.configuracionActual = this.configuracionesAireacion[0];
          this.recursosSeleccionados = [...this.configuracionesAireacion];
          
          if (this.configuracionesAireacion.length > 0) {
            this.recursosDetalles = this.configuracionesAireacion
              .filter(r => r.seleccionado)
              .map(config => ({
                recurso: config,
                tiempo: null,
                horaInicio: "",
                horaFin: "",
                minutosHombre: config.minutos_hombre || "0.00",
                detalles: config.detalles || ""
              }));
          }
          
          this.tiempoCalculado = null;
          this.diferenciaDias = 0;
          
        } else {
          this.configuracionActual = null;
          this.configuracionesAireacion = [];
          this.recursosSeleccionados = [];
          this.recursosDetalles = [{
            recurso: null,
            tiempo: null,
            horaInicio: "",
            horaFin: "",
            minutosHombre: "0.00",
            detalles: ""
          }];
        }
      } catch (error) {
        console.error('Error cargando configuración:', error);
        this.configuracionActual = null;
        this.configuracionesAireacion = [];
        this.recursosSeleccionados = [];
      }
    },

    calcularTiempoConFechas(fechaHoraInicio, fechaHoraFin) {
      if (!fechaHoraInicio || !fechaHoraFin) return null;
      
      const inicio = new Date(fechaHoraInicio);
      const fin = new Date(fechaHoraFin);
      
      if (isNaN(inicio.getTime()) || isNaN(fin.getTime())) {
        return null;
      }
      
      if (fin <= inicio) {
        this.mostrarAlerta("error", "Fecha inválida", "La fecha/hora de fin debe ser posterior a la de inicio");
        return null;
      }
      
      const diffMs = fin.getTime() - inicio.getTime();
      const minutos = Math.round(diffMs / (1000 * 60));
      
      const inicioDate = inicio.toISOString().split('T')[0];
      const finDate = fin.toISOString().split('T')[0];
      const dias = Math.abs(Math.floor((new Date(finDate) - new Date(inicioDate)) / (1000 * 60 * 60 * 24)));
      
      return {
        minutos: minutos,
        dias: dias,
        inicio: inicio,
        fin: fin
      };
    },

    calcularTiempoAuto() {
      const resultado = this.calcularTiempoConFechas(this.fechaHoraInicioAuto, this.fechaHoraFinAuto);
      
      if (resultado) {
        this.tiempoCalculado = resultado.minutos;
        this.diferenciaDias = resultado.dias;
        return resultado.minutos;
      }
      
      return null;
    },

    getFechaHoraPredeterminada(tipo) {
      const fechaActual = new Date().toISOString().slice(0, 10);
      if (tipo === 'inicio') {
        return `${fechaActual}T${this.horaPredeterminadaInicio}`;
      } else {
        return `${fechaActual}T${this.horaPredeterminadaFin}`;
      }
    },

    getFechaHoraActual(agregar15Minutos = false) {
      const ahora = new Date();
      if (agregar15Minutos) {
        ahora.setMinutes(ahora.getMinutes() + 15);
      }
      return ahora.toISOString().slice(0, 16); 
    },

    // ========== MÚLTIPLES RECURSOS METHODS ==========
    
    actualizarRecursosSeleccionados(recurso, index) {
      if (recurso.seleccionado) {
        const existe = this.recursosDetalles.find(d => d.recurso?.id === recurso.id);
        if (!existe) {
          this.recursosDetalles.push({
            recurso: recurso,
            tiempo: this.tiempoCalculado,
            horaInicio: this.fechaHoraInicioAuto,
            horaFin: this.fechaHoraFinAuto,
            minutosHombre: recurso.minutos_hombre || "0.00",
            detalles: recurso.detalles || ""
          });
        }
      } else {
        const detalleIndex = this.recursosDetalles.findIndex(d => d.recurso?.id === recurso.id);
        if (detalleIndex !== -1) {
          this.recursosDetalles.splice(detalleIndex, 1);
          if (this.recursoActivoIndex >= this.recursosDetalles.length) {
            this.recursoActivoIndex = Math.max(0, this.recursosDetalles.length - 1);
          }
        }
      }
      
      if (this.tiempoCalculado) {
        this.calcularTiempoPorRecursos();
      }
    },

    agregarRecurso() {
      this.recursosDetalles.push({
        recurso: null,
        tiempo: null,
        horaInicio: "",
        horaFin: "",
        minutosHombre: "0.00",
        detalles: ""
      });
    },

    removerRecurso(index) {
      if (this.recursosDetalles.length > 1) {
        const recursoId = this.recursosDetalles[index]?.recurso?.id;
        if (recursoId) {
          const recursoIndex = this.configuracionesAireacion.findIndex(r => r.id === recursoId);
          if (recursoIndex !== -1) {
            this.configuracionesAireacion[recursoIndex].seleccionado = false;
          }
        }
        
        this.recursosDetalles.splice(index, 1);
        if (this.recursoActivoIndex >= this.recursosDetalles.length) {
          this.recursoActivoIndex = this.recursosDetalles.length - 1;
        }
      }
    },

    seleccionarRecurso(recurso, index) {
      this.recursosDetalles[index].recurso = recurso;
      this.recursosDetalles[index].minutosHombre = recurso.minutos_hombre || "0.00";
      recurso.seleccionado = true;
    },

    calcularTiempoPorRecursos() {
      const tiempo = this.calcularTiempoAuto();
      if (tiempo) {
        this.recursosDetalles.forEach(detalle => {
          if (detalle.recurso && detalle.recurso.seleccionado) {
            detalle.tiempo = tiempo;
            detalle.horaInicio = this.fechaHoraInicioAuto;
            detalle.horaFin = this.fechaHoraFinAuto;
          }
        });
      }
      return tiempo;
    },

    calcularTiempoIndividual(index) {
      const detalle = this.recursosDetalles[index];
      if (!detalle) return;

      const resultado = this.calcularTiempoConFechas(detalle.horaInicio, detalle.horaFin);
      if (resultado) {
        detalle.tiempo = resultado.minutos;
      } else {
        if (detalle.horaInicio && detalle.horaFin) {
          // fechas inválidas o fin <= inicio — ya muestra alerta en calcularTiempoConFechas
        }
      }
    },

    calcularTotalMinutosHombre() {
      return this.recursosDetalles.reduce((total, detalle) => {
        if (detalle.recurso && detalle.tiempo) {
          const minutosHombre = parseFloat(detalle.minutosHombre) || 0;
          return total + (detalle.tiempo * minutosHombre / 60);
        }
        return total;
      }, 0);
    },

    mostrarSelectorRecursos() {
      Swal.fire({
        title: 'Seleccionar Recurso',
        html: `
          <div class="selector-recursos">
            <div class="list-group">
              ${this.configuracionesAireacion.map(recurso => `
                <a href="#" class="list-group-item list-group-item-action" 
                   onclick="window.selectRecurso(${recurso.id})">
                  <div class="d-flex justify-content-between">
                    <strong>${recurso.recurso.recurso}</strong>
                    <small class="text-muted">${recurso.minutos_hombre} min/hombre</small>
                  </div>
                </a>
              `).join('')}
            </div>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Agregar',
        cancelButtonText: 'Cancelar',
        didOpen: () => {
          window.selectRecurso = (id) => {
            const recurso = this.configuracionesAireacion.find(r => r.id === id);
            if (recurso && !this.recursosSeleccionados.find(r => r.id === id)) {
              recurso.seleccionado = true;
              this.recursosSeleccionados.push({
                ...recurso,
                seleccionado: true,
                tiempo: this.tiempoCalculado,
                horaInicio: this.fechaHoraInicioAuto,
                horaFin: this.fechaHoraFinAuto,
                detalles: ""
              });
              
              this.recursosDetalles.push({
                recurso: recurso,
                tiempo: this.tiempoCalculado,
                horaInicio: this.fechaHoraInicioAuto,
                horaFin: this.fechaHoraFinAuto,
                minutosHombre: recurso.minutos_hombre || "0.00",
                detalles: ""
              });
            }
          };
        }
      });
    },

    // ========== MANUAL CALCULATOR METHODS ==========
    
    calcularTiempoManual() {
      const resultado = this.calcularTiempoConFechas(this.fechaHoraInicioManual, this.fechaHoraFinManual);
      
      if (resultado) {
        this.tiempoManual = resultado.minutos;
        this.diferenciaDiasManual = resultado.dias;
        return resultado.minutos;
      }
      
      this.tiempoManual = null;
      this.diferenciaDiasManual = 0;
      return null;
    },
    
    aplicarCalculo() {
      if (this.tiempoManual) {
        this.tiempoAplicado = this.tiempoManual;
        
        this.mostrarAlerta("success", "Cálculo aplicado", 
          `Se han calculado ${this.tiempoManual} minutos de aireación.`);
      }
    },
    
    resetCalculadoraManual() {
      this.fechaHoraInicioManual = "";  
      this.fechaHoraFinManual = "";     
      this.tiempoManual = null;
      this.tiempoAplicado = null;
      this.diferenciaDiasManual = 0;
    },

    // ========== SAVE METHODS ==========
    
    async guardarSoloAgua() {
      if (!this.selectedSiembra || !this.user || !this.form.fecha_parametro) return;
      
      if (!this.validarAguaCompleta()) {
        return;
      }

      this.saving = true;

      try {
        const now = new Date();
        const fechaRegistro = now.toISOString().split('T')[0];
        const horaRegistro = now.toTimeString().split(' ')[0].substring(0, 5);

        const datosAgua = this.prepararDatosAgua();

        const payload = {
          registered_user_id: this.user.id,
          id_siembra: this.selectedSiembra.id,
          fecha_parametro: this.form.fecha_parametro,
          temperatura: datosAgua.temperatura,
          "12_am": datosAgua['12_am'],
          "4_am": datosAgua['4_am'], 
          "8_pm": datosAgua['8_pm'],
          tipo: "PARAMETROS_CELADOR",
          descripcion: "Parámetros de calidad del agua",
          fecha_registro: fechaRegistro,
          hora_registro: horaRegistro
        };

        await axios.post("api/parametros-agua-avanzados-prelogs", payload);
        
        this.successTitle = "Parámetros guardados";
        this.successMessage = "Parámetros de agua registrados correctamente.";
        this.showSuccessModal = true;
        
        setTimeout(() => {
          this.backToPonds();
          this.showSuccessModal = false;
        }, 2000);
        
      } catch (e) {
        console.error("Error guardando parámetros agua:", e);
        this.mostrarAlerta("error", "Error", "No se pudo guardar los parámetros de agua.");
      } finally {
        this.saving = false;
      }
    },

    async guardarCombinado() {
      if (!this.selectedSiembra || !this.user) return;

      if (!this.validarAguaCompleta()) {
        return;
      }

      this.saving = true;

      try {
        await this.guardarSoloAguaSilently();
        
        if (this.modoAireacion === 'auto') {
          await this.guardarAireacionAutomatica();
        } else if (this.modoAireacion === 'manual' && this.tiempoAplicado) {
          await this.guardarAireacionManualCombinada();
        }
        
        this.successTitle = "Registro completo guardado";
        this.successMessage = this.modoAireacion === 'auto' 
          ? `Parámetros y aireación registrados correctamente. ${this.recursosDetalles.filter(d => d.recurso && d.tiempo).length} recursos de aireación.`
          : "Parámetros y aireación calculada guardados correctamente.";
        this.showSuccessModal = true;
        
        setTimeout(() => {
          this.backToPonds();
          this.showSuccessModal = false;
        }, 2000);
        
      } catch (e) {
        console.error("Error guardando combinado:", e);
        this.mostrarAlerta("error", "Error", "No se pudo guardar el registro completo.");
      } finally {
        this.saving = false;
      }
    },

    async guardarSoloAguaSilently() {
      if (!this.selectedSiembra || !this.user || !this.form.fecha_parametro) return;

      const now = new Date();
      const fechaRegistro = now.toISOString().split('T')[0];
      const horaRegistro = now.toTimeString().split(' ')[0].substring(0, 5);

      const datosAgua = this.prepararDatosAgua();

      const payload = {
        registered_user_id: this.user.id,
        id_siembra: this.selectedSiembra.id,
        fecha_parametro: this.form.fecha_parametro || this.today,
        temperatura: datosAgua.temperatura,
        "4_am": datosAgua['4_am'],
        "12_am": datosAgua['12_am'],
        "8_pm": datosAgua['8_pm'],
        tipo: "PARAMETROS_CELADOR",
        descripcion: "Parámetros de calidad del agua",
        fecha_registro: fechaRegistro,
        hora_registro: horaRegistro
      };

      await axios.post("api/parametros-agua-avanzados-prelogs", payload);
    },

    // CORRECCIÓN: Preparar datos enviando null explícito en campos vacíos
    prepararDatosAgua() {
      return {
        temperatura: this.formatearDecimal2(this.form.temperatura),
        "4_am": this.formatearDecimal2(this.form['4_am']), 
        "12_am": this.formatearDecimal2(this.form['12_am']),
        "8_pm": this.formatearDecimal2(this.form['8_pm'])
      };
    },

    async guardarAireacionAutomatica() {
      const detallesConTiempo = this.recursosDetalles.filter(d => d.recurso && d.tiempo);
      if (!this.selectedSiembra || detallesConTiempo.length === 0) return;

      for (const detalle of detallesConTiempo) {
        const idRecurso = detalle.recurso.recurso_id || null;
        const nombreRecurso = detalle.recurso.recurso?.recurso || 'Recurso no disponible';
        const duracion = Number(detalle.tiempo) || 0;

        const payload = {
          registered_user_id: this.user.id,
          id_siembra: this.selectedSiembra.id,
          id_recurso: idRecurso,
          id_alimento: null,
          fecha_ra: this.form.fecha_parametro || this.today,
          cant_manana: duracion,   // duración real en minutos
          cant_tarde: 0,
          minutos_hombre: detalle.minutosHombre || "0",
          conv_alimenticia: null,
          detalles: `Aireación - ${nombreRecurso} | Inicio: ${this.formatFechaHora(detalle.horaInicio)} | Fin: ${this.formatFechaHora(detalle.horaFin)} | Duración: ${duracion} min`,
          tipo_actividad: 12,
          tipo: "AIREACION",
          descripcion: `Aireación mecánica - ${nombreRecurso} - ${duracion} minutos`
        };

        await axios.post("api/feeding-prelogs", payload);
      }
    },

    async guardarAireacionManualCombinada() {
      if (!this.selectedSiembra || !this.tiempoAplicado) return;

      const idRecurso = this.configuracionActual?.recurso_id || null;
      const duracion = Number(this.tiempoAplicado) || 0;
      const nombreRecurso = this.configuracionActual?.recurso?.recurso || 'Recurso';

      const payload = {
        registered_user_id: this.user.id,
        id_siembra: this.selectedSiembra.id,
        id_recurso: idRecurso,
        id_alimento: null,
        fecha_ra: this.form.fecha_parametro || this.today,
        cant_manana: duracion,   // duración real en minutos
        cant_tarde: 0,
        minutos_hombre: this.configuracionActual?.minutos_hombre || "0",
        conv_alimenticia: null,
        detalles: `Aireación - ${nombreRecurso} | Inicio: ${this.formatFechaHora(this.fechaHoraInicioManual)} | Fin: ${this.formatFechaHora(this.fechaHoraFinManual)} | Duración: ${duracion} min`,
        tipo_actividad: 12,
        tipo: "AIREACION",
        descripcion: `Aireación mecánica - ${nombreRecurso} - ${duracion} minutos`
      };

      await axios.post("api/feeding-prelogs", payload);
    },

    async guardarSoloAireacion() {
      if (!this.selectedSiembra || this.recursosDetalles.filter(d => d.recurso && d.tiempo).length === 0) {
        this.mostrarAlerta("warning", "Datos incompletos", "Debe calcular el tiempo de al menos un recurso antes de guardar.");
        return;
      }

      this.saving = true;

      try {
        await this.guardarAireacionAutomatica();
        
        this.successTitle = "Aireación guardada";
        this.successMessage = `${this.recursosDetalles.filter(d => d.tiempo).length} recurso(s) de aireación registrados`;
        this.showSuccessModal = true;
        
        setTimeout(() => {
          this.backToPonds();
          this.showSuccessModal = false;
        }, 2000);
        
      } catch (error) {
        console.error("Error guardando aireación:", error);
        this.mostrarAlerta("error", "Error", "No se pudo guardar la aireación.");
      } finally {
        this.saving = false;
      }
    },

    async guardarSoloAireacionManual() {
      if (!this.selectedSiembra || !this.tiempoAplicado) {
        this.mostrarAlerta("warning", "Datos incompletos", "Aplique primero un cálculo.");
        return;
      }

      this.saving = true;

      try {
        const idRecurso = this.configuracionActual?.recurso_id || null;
        const duracion = Number(this.tiempoAplicado) || 0;
        const nombreRecurso = this.configuracionActual?.recurso?.recurso || 'Recurso';

        const payload = {
          registered_user_id: this.user.id,
          id_siembra: this.selectedSiembra.id,
          id_recurso: idRecurso,
          id_alimento: null,
          fecha_ra: this.getFechaFromDateTime(this.fechaHoraInicioManual) || this.today,
          cant_manana: duracion,   // duración real en minutos
          cant_tarde: 0,
          minutos_hombre: this.configuracionActual?.minutos_hombre || "0",
          conv_alimenticia: null,
          detalles: `Aireación - ${nombreRecurso} | Inicio: ${this.formatFechaHora(this.fechaHoraInicioManual)} | Fin: ${this.formatFechaHora(this.fechaHoraFinManual)} | Duración: ${duracion} min`,
          tipo_actividad: 12,
          tipo: "AIREACION",
          descripcion: `Aireación mecánica - ${nombreRecurso} - ${duracion} minutos`
        };

        await axios.post("api/feeding-prelogs", payload);
        
        this.successTitle = "Aireación calculada guardada";
        this.successMessage = `${this.tiempoAplicado} minutos de aireación calculados manualmente registrados`;
        this.showSuccessModal = true;
        
        setTimeout(() => {
          this.backToPonds();
          this.showSuccessModal = false;
        }, 2000);
        
      } catch (error) {
        console.error("Error guardando aireación calculada:", error);
        this.mostrarAlerta("error", "Error", "No se pudo guardar la aireación.");
      } finally {
        this.saving = false;
      }
    },

    // History
    async cargarHistorial() {
      if (!this.selectedSiembra) return;
      this.historialLoading = true;
      try {
        const { data } = await axios.get("api/parametros-agua-avanzados-prelogs", {
          params: { 
            fecha_parametro: this.form.fecha_parametro || this.today, 
            id_siembra: this.selectedSiembra.id, 
            no_paginate: 1 
          }
        });
        const list = data && data.data ? data.data : (Array.isArray(data) ? data : []);
        this.historial = list || [];
      } catch (e) {
        console.error("Error cargando historial:", e);
        this.historial = [];
      } finally {
        this.historialLoading = false;
      }
    },

    async cargarHistorialAireacion() {
      if (!this.selectedSiembra) return;
      this.historialLoading = true;
      try {
        const { data } = await axios.get("api/feeding-prelogs", {
          params: { 
            id_siembra: this.selectedSiembra.id,
            fecha_ra: this.form.fecha_parametro || this.today,
            tipo: "AIREACION",
            no_paginate: 1 
          }
        });
        
        const list = Array.isArray(data) ? data : (data?.data || []);
        this.historialAireacion = list || [];
      } catch (e) {
        console.error("Error cargando historial aireación:", e);
        this.historialAireacion = [];
      } finally {
        this.historialLoading = false;
      }
    },

    async buscarRegistroExistente() {
      if (!this.selectedSiembra || !this.form.fecha_parametro) return;
      try {
        const { data } = await axios.post("api/parametros-agua-avanzados-prelogs/buscar", {
          fecha_parametro: this.form.fecha_parametro,
          id_siembra: this.selectedSiembra.id,
          registered_user_id: this.user?.id,
        });

        if (data) {
          this.form.temperatura = data.temperatura || "";
          this.form['12_am'] = data['12_am'] || "";
          this.form['4_am'] = data['4_am'] || "";
          this.form['8_pm'] = data['8_pm'] || "";
        }
      } catch (e) {
        // No existing record
      }
    },

    cargarRegistroEnFormulario(log) {
      this.modoRapido = 'agua';
      this.form.fecha_parametro = log.fecha_parametro;
      this.form.temperatura = log.temperatura || "";
      this.form['12_am'] = log['12_am'] || "";
      this.form['4_am'] = log['4_am'] || "";
      this.form['8_pm'] = log['8_pm'] || "";
    },

    // Utilities
    closeSuccessModal() { 
      this.showSuccessModal = false; 
    },

    setToday() {
      const hoy = new Date();
      const mes = String(hoy.getMonth() + 1).padStart(2, "0");
      const dia = String(hoy.getDate()).padStart(2, "0");
      this.today = `${hoy.getFullYear()}-${mes}-${dia}`;
    },

    formatDate(dateStr) {
      if (!dateStr) return '';
      const date = new Date(dateStr);
      return date.toLocaleDateString('es-ES', { 
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric' 
      });
    },

    formatFechaHora(dateTimeStr) {
      if (!dateTimeStr) return '';
      const date = new Date(dateTimeStr);
      return date.toLocaleString('es-ES', { 
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },

    getFechaFromDateTime(dateTimeStr) {
      if (!dateTimeStr) return '';
      return dateTimeStr.split('T')[0];
    },

    getHoraFromDateTime(dateTimeStr) {
      if (!dateTimeStr) return '';
      return dateTimeStr.split('T')[1]?.substring(0, 5) || '';
    },

    // CORRECCIÓN: Si el valor es vacío (""), devuelve estrictamente null
    formatearDecimal2(valor) {
      if (valor === 0 || valor === "0") return 0; 
      if (valor === "" || valor === null || valor === undefined || isNaN(valor)) return null;
      return parseFloat(parseFloat(valor).toFixed(2));
    },

    formatearDecimal(valor) {
      if (valor === null || valor === '' || isNaN(valor)) return null;
      const num = parseFloat(valor);
      return parseFloat(num.toFixed(3));
    },

    mostrarAlerta(icon, title, text) {
      Swal.fire({
        icon: icon,
        title: title,
        text: text,
        timer: 3000,
        showConfirmButton: false
      });
    }
  },

  mounted() {
    this.setToday();
    this.cargarSiembras();
    this.resetForm();
  },

  beforeDestroy() {
    this.limpiarTimerInactividad();
  },
};
</script>

<style scoped>
/* =========================================
 * AERADORES: tarjetas autocontenidas
 * ========================================= */
.aeradores-container {
  margin-top: 4px;
}

.aeradores-hint {
  font-size: 13px;
  color: #94a3b8;
}

.aerador-card {
  background: rgba(10, 20, 42, 0.85);
  border: 1px solid rgba(100, 116, 139, 0.3);
  border-radius: 12px;
  overflow: hidden;
  transition: border-color 0.2s;
}

.aerador-activo {
  border-color: rgba(52, 211, 153, 0.5) !important;
  box-shadow: 0 0 0 1px rgba(52, 211, 153, 0.15);
}

.aerador-cabecera {
  display: block;
  padding: 14px 16px;
  cursor: pointer;
  user-select: none;
  transition: background 0.2s;
}

.aerador-cabecera:hover {
  background: rgba(52, 211, 153, 0.05);
}

.aerador-chk {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: #34d399;
  flex-shrink: 0;
}

.aerador-nombre {
  font-weight: 600;
  color: #f1f5f9;
  font-size: 15px;
}

.aerador-meta {
  color: #64748b;
  font-size: 12px;
}

.aerador-badge-activo {
  background: rgba(52, 211, 153, 0.15);
  color: #34d399;
  border: 1px solid rgba(52, 211, 153, 0.4);
  border-radius: 20px;
  font-size: 12px;
  padding: 3px 10px;
  white-space: nowrap;
}

.aerador-config {
  border-top: 1px solid rgba(52, 211, 153, 0.2);
  background: rgba(2, 6, 23, 0.6);
  padding: 14px 16px;
}

.aerador-tiempo-row label {
  display: block;
  margin-bottom: 4px;
  font-size: 12px;
  font-weight: 500;
}

.aerador-time-label.inicio { color: #6ee7b7; }
.aerador-time-label.fin    { color: #fca5a5; }

.aerador-resultado {
  font-size: 13px;
  color: #34d399;
  background: rgba(52, 211, 153, 0.08);
  border: 1px solid rgba(52, 211, 153, 0.2);
  border-radius: 6px;
  padding: 6px 10px;
}

.aerador-resultado strong {
  font-size: 16px;
}

.aerador-resultado .small,
.aerador-resultado span {
  color: #94a3b8;
  font-size: 11px;
}

.aerador-hint {
  font-size: 12px;
  color: #64748b;
  padding: 4px 0;
}

.aerador-obs {
  margin-top: 4px;
}

/* Resumen final */
.aeradores-resumen {
  background: rgba(2, 6, 23, 0.7);
  border: 1px solid rgba(52, 211, 153, 0.2);
  border-radius: 8px;
  padding: 12px 16px;
}

.resumen-title {
  color: #94a3b8;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.resumen-fila {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 5px 0;
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
  font-size: 13px;
  color: #e5e7eb;
}

.resumen-nombre { flex: 1; }
.resumen-tiempo { color: #34d399; font-weight: 600; }

.resumen-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 8px;
  margin-top: 4px;
  font-size: 14px;
  color: #94a3b8;
}

.resumen-total strong {
  color: #f9fafb;
  font-size: 16px;
}

/* =========================================
 * INPUTS LEGIBLES EN TEMA OSCURO
 * ========================================= */
.input-dark,
.input-dark.form-control,
.input-dark.form-control-sm {
  background-color: #1e2d3d !important;
  border: 1px solid rgba(52, 211, 153, 0.3) !important;
  color: #f1f5f9 !important;
  color-scheme: dark;
}

.input-dark:focus,
.input-dark.form-control:focus,
.input-dark.form-control-sm:focus {
  background-color: #243447 !important;
  border-color: #34d399 !important;
  box-shadow: 0 0 0 0.15rem rgba(52, 211, 153, 0.25) !important;
  color: #f9fafb !important;
  outline: none;
}

.input-dark::placeholder,
.input-dark.form-control::placeholder {
  color: rgba(148, 163, 184, 0.55) !important;
}

/* Todos los form-control dentro de este componente */
.form-control,
.form-control-sm,
select.form-control {
  background-color: #1e2d3d !important;
  border-color: rgba(100, 116, 139, 0.4) !important;
  color: #f1f5f9 !important;
  color-scheme: dark;
}

.form-control:focus,
.form-control-sm:focus {
  background-color: #243447 !important;
  border-color: #34d399 !important;
  box-shadow: 0 0 0 0.15rem rgba(52, 211, 153, 0.2) !important;
  color: #f9fafb !important;
}

.form-control::placeholder {
  color: rgba(148, 163, 184, 0.55) !important;
}

/* =========================================
 * CARDS OSCURAS
 * ========================================= */
.card-dark {
  background-color: rgba(15, 23, 42, 0.95) !important;
  border: 1px solid rgba(52, 211, 153, 0.2) !important;
}

.card-header-dark {
  background-color: rgba(5, 15, 35, 0.95) !important;
  border-bottom: 1px solid rgba(52, 211, 153, 0.2) !important;
  padding: 10px 16px;
}

.card-body-dark {
  background-color: rgba(10, 20, 40, 0.9) !important;
  padding: 16px;
}

/* Horario individual de recurso */
.horario-recurso {
  background: rgba(2, 6, 23, 0.6);
  border: 1px solid rgba(52, 211, 153, 0.2);
  border-radius: 8px;
  padding: 12px;
}

/* =========================================
 * ALERTAS EN TEMA OSCURO
 * ========================================= */
.alert-info {
  background-color: rgba(6, 45, 80, 0.7) !important;
  border-color: rgba(56, 189, 248, 0.3) !important;
  color: #bae6fd !important;
}

.alert-info strong,
.alert-info .strong {
  color: #e0f2fe !important;
}

.alert-info-dark {
  background-color: rgba(6, 45, 80, 0.7);
  border: 1px solid rgba(56, 189, 248, 0.3);
  border-radius: 6px;
  color: #bae6fd;
  padding: 10px 14px;
}

.alert-info-dark strong {
  color: #e0f2fe;
}

.alert-success {
  background-color: rgba(5, 40, 22, 0.7) !important;
  border-color: rgba(34, 197, 94, 0.3) !important;
  color: #bbf7d0 !important;
}

.alert-success strong,
.alert-success .strong {
  color: #dcfce7 !important;
}

.alert-warning {
  background-color: rgba(60, 35, 5, 0.75) !important;
  border-color: rgba(251, 191, 36, 0.35) !important;
  color: #fde68a !important;
}

/* =========================================
 * RESULTADOS Y CALCULADORAS (reemplaza bg-light)
 * ========================================= */
.resultado-instantaneo,
.resultado-calculado,
.calculadora-container,
.resumen-detallado,
.configuracion-disponible .alert {
  background-color: rgba(5, 15, 35, 0.95) !important;
  border-color: rgba(52, 211, 153, 0.25) !important;
  color: #e5e7eb !important;
}

.resultado-instantaneo .h2,
.resultado-instantaneo .display-4,
.resultado-calculado .display-4,
.resultado-calculado .display-2 {
  color: #34d399 !important;
}

.resultado-instantaneo .text-muted,
.resultado-calculado .text-muted,
.resultado-calculado h5.text-muted {
  color: #94a3b8 !important;
}

/* =========================================
 * ESTILOS PARA EL SELECTOR DE MODO
 * ========================================= */
.modo-selector {
  margin-left: auto;
}

.modo-selector .btn-group {
  border-radius: 20px;
  overflow: hidden;
}

.modo-selector .btn {
  border-radius: 0;
  padding: 4px 12px;
  font-size: 12px;
}

.modo-selector .btn:first-child {
  border-top-left-radius: 20px;
  border-bottom-left-radius: 20px;
}

.modo-selector .btn:last-child {
  border-top-right-radius: 20px;
  border-bottom-right-radius: 20px;
}

.modo-selector .btn.active {
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.3);
}

.modo-selector-container .btn-group {
  border-radius: 8px;
  overflow: hidden;
}

.modo-selector-container .btn {
  padding: 8px 12px;
}

/* Estilos para la calculadora manual */
.calculadora-manual .form-control-sm,
.calculadora-completa .form-control {
  background: rgba(2, 6, 23, 0.8);
  border-color: rgba(148, 163, 184, 0.3);
  color: #f9fafb;
}

.calculadora-completa .form-control-lg {
  font-size: 1.5rem;
  height: calc(2.5em + 1rem);
}

.calculadora-container {
  background: rgba(2, 6, 23, 0.9) !important;
  border-color: rgba(52, 211, 153, 0.3) !important;
}

.resultado-calculado {
  background: rgba(2, 6, 23, 0.95) !important;
  border-color: rgba(52, 211, 153, 0.5) !important;
}

.resumen-guardar .resumen-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}

.resumen-detallado {
  background: rgba(2, 6, 23, 0.9) !important;
  border-color: rgba(52, 211, 153, 0.3) !important;
}

/* Estilos para flecha entre campos */
.calculadora-container .fa-arrow-right {
  color: #34d399;
  opacity: 0.7;
}

/* Ajustes responsivos */
@media (max-width: 768px) {
  .resumen-guardar .resumen-grid {
    grid-template-columns: 1fr;
  }
  
  .calculadora-completa .row > div {
    margin-bottom: 1rem;
  }
  
  .calculadora-completa .form-control-lg {
    font-size: 1.25rem;
    height: calc(2em + 1rem);
  }
}

/* Animación para resultado */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.resultado-calculado {
  animation: fadeInUp 0.3s ease;
}

/* Estilos para recursos múltiples */
.recursos-lista {
  background: rgba(2, 6, 23, 0.8);
  border-radius: 8px;
  padding: 16px;
  border: 1px solid rgba(148, 163, 184, 0.3);
}

.recurso-item {
  padding: 12px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
}

.recurso-item:last-child {
  border-bottom: none;
}

.detalles-recursos .nav-tabs {
  border-bottom: 1px solid rgba(148, 163, 184, 0.3);
}

.detalles-recursos .nav-link {
  color: #94a3b8;
  border: 1px solid transparent;
  border-bottom: none;
  margin-bottom: -1px;
  background: rgba(2, 6, 23, 0.5);
}

.detalles-recursos .nav-link.active {
  color: #34d399;
  background: rgba(15, 23, 42, 0.9);
  border-color: rgba(148, 163, 184, 0.3);
  border-bottom-color: transparent;
}

.detalle-recurso .card {
  background: rgba(15, 23, 42, 0.9);
  border-color: rgba(148, 163, 184, 0.3);
}

.table-dark {
  background: rgba(2, 6, 23, 0.9);
  color: #f9fafb;
}

.table-dark th {
  border-color: rgba(148, 163, 184, 0.3);
  color: #94a3b8;
}

.table-dark td {
  border-color: rgba(148, 163, 184, 0.2);
}

/* Main container */
.registrador-app-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

/* Top bar */
.top-bar {
  background: linear-gradient(135deg, #0f172a 0%, #020617 100%);
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 24px;
  border: 1px solid rgba(148, 163, 184, 0.24);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.top-section-label {
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: rgba(148, 163, 184, 0.9);
  margin-bottom: 8px;
}

.top-title-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}

.top-title {
  font-size: 24px;
  font-weight: 700;
  color: #f9fafb;
  margin: 0;
}

.ghost-button {
  background: rgba(34, 197, 94, 0.15);
  border: 1px solid rgba(34, 197, 94, 0.5);
  color: #34d399;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 8px;
}

.ghost-button:hover {
  background: rgba(34, 197, 94, 0.25);
  border-color: #34d399;
  transform: translateY(-1px);
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
  color: #e5e7eb;
  font-size: 14px;
}

.user-name {
  font-weight: 500;
}

.user-role {
  background: rgba(34, 197, 94, 0.15);
  color: #34d399;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 12px;
}

.idle-bar-wrapper {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.idle-bar-bg {
  width: 100%;
  height: 6px;
  background: rgba(15, 23, 42, 0.9);
  border-radius: 3px;
  overflow: hidden;
}

.idle-bar-fg {
  height: 100%;
  background: linear-gradient(90deg, #22c55e 0%, #14b8a6 100%);
  border-radius: 3px;
  transition: width 1s linear;
}

.idle-text {
  font-size: 12px;
  color: rgba(148, 163, 184, 0.9);
  text-align: right;
}

/* Wizard steps */
.wizard-steps {
  display: flex;
  gap: 12px;
  margin-bottom: 32px;
}

.step {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  background: rgba(15, 23, 42, 0.9);
  border: 1px solid rgba(148, 163, 184, 0.3);
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s;
}

.step.disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.step.active {
  background: linear-gradient(135deg, #22c55e 0%, #14b8a6 100%);
  border-color: #34d399;
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(34, 197, 94, 0.3);
}

.step-number {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  color: #f9fafb;
}

.step.active .step-number {
  background: rgba(2, 6, 23, 0.2);
  color: #020617;
}

.step-label {
  font-weight: 500;
  color: #e5e7eb;
}

.step.active .step-label {
  color: #020617;
  font-weight: 600;
}

/* Step content */
.step-content {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.step-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.step-header h2 {
  font-size: 20px;
  color: #f9fafb;
  margin: 0;
}

.step-info .badge {
  background: rgba(34, 197, 94, 0.15);
  color: #34d399;
  padding: 4px 12px;
  border-radius: 16px;
  font-size: 14px;
  font-weight: 500;
}

/* Filter section */
.filter-section {
  margin-bottom: 24px;
}

.input-group {
  position: relative;
}

.input-icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
}

.text-input {
  width: 100%;
  padding: 12px 16px 12px 44px;
  background: rgba(15, 23, 42, 0.9);
  border: 1px solid rgba(148, 163, 184, 0.3);
  border-radius: 12px;
  color: #f9fafb;
  font-size: 16px;
  transition: all 0.3s;
}

.text-input:focus {
  outline: none;
  border-color: #34d399;
  box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.1);
}

/* Pond grid */
.pond-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
}

.pond-card {
  background: rgba(15, 23, 42, 0.9);
  border: 1px solid rgba(148, 163, 184, 0.3);
  border-radius: 12px;
  padding: 20px;
  cursor: pointer;
  transition: all 0.3s;
}

.pond-card:hover {
  border-color: #34d399;
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(34, 197, 94, 0.2);
}

.pond-card-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.pond-number {
  font-size: 32px;
  font-weight: 700;
  color: #f9fafb;
  margin-bottom: 8px;
}

.pond-info {
  width: 100%;
}

.pond-name {
  font-size: 14px;
  color: #94a3b8;
  margin-bottom: 12px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.pond-action {
  color: #34d399;
  font-size: 14px;
}

.empty-state {
  grid-column: 1 / -1;
  text-align: center;
  padding: 48px 20px;
  color: #94a3b8;
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 16px;
  opacity: 0.5;
}

/* Back button */
.back-button {
  display: flex;
  align-items: center;
  gap: 8px;
  background: transparent;
  border: none;
  color: #34d399;
  font-size: 16px;
  cursor: pointer;
  padding: 8px 0;
  margin-bottom: 20px;
  transition: all 0.3s;
}

.back-button:hover {
  color: #22c55e;
  transform: translateX(-4px);
}

/* Selected pond info */
.selected-pond-info {
  display: flex;
  gap: 12px;
  margin-bottom: 24px;
  flex-wrap: wrap;
}

.pond-badge {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(15, 23, 42, 0.9);
  border: 1px solid rgba(148, 163, 184, 0.3);
  border-radius: 20px;
  padding: 8px 16px;
  font-size: 14px;
  color: #e5e7eb;
}

.pond-badge i {
  color: #34d399;
}

/* MODO RÁPIDO SELECTOR */
.modo-rapido-selector {
  margin-bottom: 24px;
}

.modo-buttons {
  display: flex;
  gap: 12px;
  background: rgba(15, 23, 42, 0.9);
  border: 1px solid rgba(148, 163, 184, 0.3);
  border-radius: 12px;
  padding: 8px;
}

.modo-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 16px;
  background: transparent;
  border: none;
  border-radius: 8px;
  color: #94a3b8;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
}

.modo-btn:hover {
  color: #e5e7eb;
  background: rgba(148, 163, 184, 0.1);
}

.modo-btn.active {
  background: linear-gradient(135deg, #22c55e 0%, #14b8a6 100%);
  color: #020617;
  box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
}

/* Form containers */
.form-container {
  animation: fadeIn 0.3s ease;
}

.form-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.3);
}

.form-header h3 {
  font-size: 18px;
  color: #f9fafb;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.form-header i {
  color: #34d399;
}

.date-display {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #94a3b8;
  font-size: 14px;
}

.form-card {
  background: rgba(15, 23, 42, 0.9);
  border: 1px solid rgba(148, 163, 184, 0.3);
  border-radius: 16px;
  padding: 24px;
}

/* Form sections */
.form-section {
  margin-bottom: 24px;
}

.section-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 500;
  color: #e5e7eb;
  margin-bottom: 12px;
}

.section-label i {
  color: #34d399;
}

.form-input {
  width: 100%;
  padding: 12px 16px;
  background: rgba(2, 6, 23, 0.8);
  border: 1px solid rgba(148, 163, 184, 0.3);
  border-radius: 8px;
  color: #f9fafb;
  font-size: 16px;
  transition: all 0.3s;
}

.form-input:focus {
  outline: none;
  border-color: #34d399;
  box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.1);
}

textarea.form-input {
  min-height: 80px;
  resize: vertical;
}

.input-with-unit {
  position: relative;
}

.input-unit {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 14px;
}

.input-hint {
  font-size: 12px;
  color: #94a3b8;
  margin-top: 8px;
}

/* Oxygen grid */
.oxygen-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 16px;
  margin-bottom: 8px;
}

.oxygen-time label {
  display: block;
  font-size: 12px;
  color: #94a3b8;
  margin-bottom: 8px;
}

/* Form actions */
.form-actions {
  display: flex;
  gap: 16px;
  margin-top: 32px;
  padding-top: 24px;
  border-top: 1px solid rgba(148, 163, 184, 0.3);
}

.primary-button {
  flex: 1;
  padding: 16px 24px;
  background: linear-gradient(135deg, #22c55e 0%, #14b8a6 100%);
  border: none;
  border-radius: 12px;
  color: #020617;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  transition: all 0.3s;
}

.primary-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(34, 197, 94, 0.4);
}

.primary-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.primary-button.large {
  padding: 20px 32px;
  font-size: 18px;
}

.secondary-button {
  flex: 1;
  padding: 16px 24px;
  background: rgba(15, 23, 42, 0.9);
  border: 1px solid rgba(148, 163, 184, 0.3);
  border-radius: 12px;
  color: #e5e7eb;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  transition: all 0.3s;
}

.secondary-button:hover {
  border-color: #34d399;
  color: #34d399;
  transform: translateY(-2px);
}

/* MODO COMBINADO */
.combinado-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.3);
}

.combinado-title h3 {
  font-size: 18px;
  color: #f9fafb;
  margin: 0 0 4px 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.combinado-title small {
  color: #34d399;
}

.combinado-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
  margin-bottom: 24px;
}

.combinado-col {
  display: flex;
  flex-direction: column;
}

.combinado-card {
  background: rgba(15, 23, 42, 0.9);
  border: 1px solid rgba(148, 163, 184, 0.3);
  border-radius: 16px;
  overflow: hidden;
  flex: 1;
}

.combinado-card.aireacion-auto {
  border-color: rgba(52, 211, 153, 0.5);
  border-width: 2px;
}

.combinado-card .card-header {
  padding: 16px 24px;
  background: rgba(2, 6, 23, 0.8);
  border-bottom: 1px solid rgba(148, 163, 184, 0.3);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.combinado-card .card-header h4 {
  font-size: 16px;
  color: #f9fafb;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.auto-badge {
  background: linear-gradient(135deg, #22c55e 0%, #14b8a6 100%);
  color: #020617;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.combinado-card .card-body {
  padding: 24px;
}

.form-section-sm {
  margin-bottom: 16px;
}

.form-section-sm label {
  display: block;
  font-size: 12px;
  color: #94a3b8;
  margin-bottom: 8px;
}

.form-input-sm {
  width: 100%;
  padding: 10px 12px;
  background: rgba(2, 6, 23, 0.8);
  border: 1px solid rgba(148, 163, 184, 0.3);
  border-radius: 8px;
  color: #f9fafb;
  font-size: 14px;
}

.input-with-unit-sm {
  position: relative;
}

.input-unit-sm {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 12px;
}

/* Configuración detectada */
.config-detected .alert-success {
  background: rgba(52, 211, 153, 0.1);
  border-color: rgba(52, 211, 153, 0.3);
}

/* Calculadora rápida */
.time-inputs-rapidos .form-control-sm {
  background: rgba(2, 6, 23, 0.8);
  border-color: rgba(148, 163, 184, 0.3);
  color: #f9fafb;
}

.resultado-instantaneo {
  background: rgba(2, 6, 23, 0.8) !important;
  border-color: rgba(52, 211, 153, 0.3) !important;
}

.resultado-instantaneo .display-4 {
  font-size: 3.5rem;
  font-weight: 700;
}

/* Resumen grid */
.resumen-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

.resumen-item {
  padding: 12px;
  background: rgba(2, 6, 23, 0.8);
  border-radius: 8px;
  border-left: 3px solid #34d399;
}

.resumen-item small {
  display: block;
  color: #94a3b8;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 4px;
}

.resumen-item strong {
  color: #f9fafb;
  font-size: 14px;
}

/* History */
.history-container {
  animation: fadeIn 0.3s ease;
}

.history-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.3);
}

.history-header h3 {
  font-size: 18px;
  color: #f9fafb;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.history-tabs {
  display: flex;
  gap: 8px;
  background: rgba(15, 23, 42, 0.9);
  border: 1px solid rgba(148, 163, 184, 0.3);
  border-radius: 8px;
  padding: 4px;
}

.history-tab {
  padding: 8px 16px;
  background: transparent;
  border: none;
  border-radius: 6px;
  color: #94a3b8;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s;
}

.history-tab.active {
  background: linear-gradient(135deg, #22c55e 0%, #14b8a6 100%);
  color: #020617;
}

.loading-state {
  text-align: center;
  padding: 48px 20px;
  color: #94a3b8;
}

.loading-state i {
  font-size: 32px;
  margin-bottom: 16px;
}

.history-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.history-item {
  background: rgba(15, 23, 42, 0.9);
  border: 1px solid rgba(148, 163, 184, 0.3);
  border-radius: 12px;
  padding: 20px;
  transition: all 0.3s;
  cursor: pointer;
}

.history-item:hover {
  border-color: #34d399;
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(34, 197, 94, 0.2);
}

.history-item-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.history-date {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #94a3b8;
  font-size: 14px;
}

.history-action {
  font-size: 12px;
  color: #34d399;
}

.history-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
  color: #f9fafb;
}

.history-data-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 16px;
}

.data-item {
  display: flex;
  flex-direction: column;
}

.data-item small {
  color: #94a3b8;
  font-size: 12px;
  margin-bottom: 4px;
}

.data-item strong {
  color: #f9fafb;
  font-size: 16px;
}

.history-observation {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid rgba(148, 163, 184, 0.2);
  color: #94a3b8;
  font-size: 14px;
}

.empty-history {
  text-align: center;
  padding: 48px 20px;
  color: #94a3b8;
}

.empty-history i {
  font-size: 48px;
  margin-bottom: 16px;
  opacity: 0.5;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(2, 6, 23, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-content {
  background: linear-gradient(135deg, #0f172a 0%, #020617 100%);
  border-radius: 24px;
  padding: 32px;
  text-align: center;
  max-width: 400px;
  width: 100%;
  border: 1px solid rgba(148, 163, 184, 0.3);
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
}

.modal-icon {
  font-size: 64px;
  color: #22c55e;
  margin-bottom: 16px;
}

.modal-title {
  font-size: 24px;
  color: #f9fafb;
  margin-bottom: 12px;
}

.modal-message {
  color: #94a3b8;
  margin-bottom: 24px;
  line-height: 1.6;
}

.modal-button {
  width: 100%;
  padding: 16px 24px;
  background: linear-gradient(135deg, #22c55e 0%, #14b8a6 100%);
  border: none;
  border-radius: 12px;
  color: #020617;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.modal-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(34, 197, 94, 0.4);
}

/* Responsive */
@media (max-width: 768px) {
  .registrador-app-container {
    padding: 16px;
  }
  
  .top-bar {
    padding: 20px;
  }
  
  .top-title {
    font-size: 20px;
  }
  
  .wizard-steps {
    flex-direction: column;
  }
  
  .pond-grid {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  }
  
  .modo-buttons {
    flex-direction: column;
  }
  
  .combinado-grid {
    grid-template-columns: 1fr;
  }
  
  .oxygen-grid {
    grid-template-columns: 1fr;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .resumen-grid {
    grid-template-columns: 1fr;
  }
  
  .history-header {
    flex-direction: column;
    gap: 16px;
    align-items: flex-start;
  }
  
  .history-data-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .history-data-grid {
    grid-template-columns: 1fr;
  }
  
  .pond-grid {
    grid-template-columns: 1fr;
  }
  
  .form-card {
    padding: 16px;
  }
  
  .combinado-card .card-body {
    padding: 16px;
  }
}

/* Contenedor principal para inputs datetime */
.datetime-container {
  position: relative;
  margin-bottom: 1rem;
}

/* Estilo base para inputs datetime */
.datetime-input {
  width: 100%;
  padding: 14px 16px;
  background: rgba(2, 6, 23, 0.9);
  border: 2px solid rgba(148, 163, 184, 0.3);
  border-radius: 12px;
  color: #f9fafb;
  font-size: 16px;
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Focus state */
.datetime-input:focus {
  outline: none;
  border-color: #34d399;
  box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.15);
  transform: translateY(-2px);
}

/* Hover state */
.datetime-input:hover:not(:focus) {
  border-color: rgba(148, 163, 184, 0.5);
  background: rgba(2, 6, 23, 0.95);
}

/* Placeholder styling */
.datetime-input::placeholder {
  color: rgba(148, 163, 184, 0.6);
  font-style: italic;
}

/* Icono personalizado para el calendario */
.datetime-input-wrapper {
  position: relative;
}

.datetime-input-wrapper::before {
  content: "📅";
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 18px;
  color: #34d399;
  pointer-events: none;
  z-index: 1;
}

.datetime-input-wrapper input {
  padding-right: 50px !important;
}

/* Calendario personalizado (para navegadores que lo soportan) */
.datetime-input::-webkit-calendar-picker-indicator {
  background: transparent;
  color: #34d399;
  cursor: pointer;
  opacity: 0.7;
  filter: invert(60%) sepia(90%) saturate(400%) hue-rotate(120deg);
  padding: 5px;
  margin-left: 5px;
}

.datetime-input::-webkit-calendar-picker-indicator:hover {
  opacity: 1;
  transform: scale(1.1);
}

/* Estilo para el popup del calendario */
.datetime-input::-webkit-datetime-edit {
  padding: 2px;
  color: #f9fafb;
}

.datetime-input::-webkit-datetime-edit-fields-wrapper {
  background: transparent;
}

.datetime-input::-webkit-datetime-edit-text {
  color: #34d399;
  padding: 0 4px;
}

.datetime-input::-webkit-datetime-edit-month-field,
.datetime-input::-webkit-datetime-edit-day-field,
.datetime-input::-webkit-datetime-edit-year-field,
.datetime-input::-webkit-datetime-edit-hour-field,
.datetime-input::-webkit-datetime-edit-minute-field {
  color: #f9fafb;
  background: rgba(52, 211, 153, 0.1);
  border-radius: 4px;
  padding: 2px 4px;
}

/* Estilo para el label */
.datetime-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 500;
  color: #e5e7eb;
  margin-bottom: 8px;
}

.datetime-label i {
  color: #34d399;
  font-size: 16px;
}

/* Estado válido/inválido */
.datetime-input:valid {
  border-color: rgba(52, 211, 153, 0.5);
}

.datetime-input:invalid:not(:placeholder-shown) {
  border-color: #ef4444;
}

/* Variante para inputs grandes */
.datetime-input-lg {
  padding: 18px 20px;
  font-size: 18px;
  border-radius: 16px;
}

/* Variante para inputs pequeños */
.datetime-input-sm {
  padding: 10px 12px;
  font-size: 14px;
  border-radius: 10px;
}

/* Efecto de pulso cuando está vacío */
.datetime-input:empty:focus {
  animation: pulse-empty 2s infinite;
}

@keyframes pulse-empty {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.2);
  }
  50% {
    box-shadow: 0 0 0 6px rgba(52, 211, 153, 0);
  }
}

/* Efecto de carga cuando se calcula tiempo */
.datetime-input.calculating {
  position: relative;
  overflow: hidden;
}

.datetime-input.calculating::after {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 2px;
  background: linear-gradient(90deg, transparent, #34d399, transparent);
  animation: loading-shimmer 1.5s infinite;
}

@keyframes loading-shimmer {
  0% { left: -100%; }
  100% { left: 100%; }
}

/* Responsive */
@media (max-width: 768px) {
  .datetime-input {
    padding: 12px 14px;
    font-size: 16px;
  }
  
  .datetime-input-lg {
    padding: 16px 18px;
    font-size: 16px;
  }
}

/* PARA EL CALCULADOR MANUAL COMPLETO */
.calculadora-container .datetime-input {
  background: rgba(2, 6, 23, 0.95);
  border: 2px solid rgba(52, 211, 153, 0.3);
  font-size: 18px;
  text-align: center;
}

.calculadora-container .datetime-input:focus {
  border-color: #34d399;
  box-shadow: 0 0 0 6px rgba(52, 211, 153, 0.2);
}

/* Flecha entre campos de fecha */
.datetime-arrow {
  color: #34d399;
  font-size: 24px;
  opacity: 0.8;
  animation: arrow-pulse 3s infinite;
}

@keyframes arrow-pulse {
  0%, 100% { opacity: 0.6; transform: scale(1); }
  50% { opacity: 1; transform: scale(1.1); }
}

/* Estilo para inputs agrupados (inicio y fin) */
.datetime-group {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  align-items: center;
  gap: 20px;
  margin: 20px 0;
}

@media (max-width: 768px) {
  .datetime-group {
    grid-template-columns: 1fr;
    gap: 15px;
  }
  
  .datetime-arrow {
    transform: rotate(90deg);
  }
}

/* Botón de limpiar fecha */
.datetime-clear {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(239, 68, 68, 0.2);
  border: none;
  color: #ef4444;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  opacity: 0;
  transition: opacity 0.3s;
}

.datetime-container:hover .datetime-clear {
  opacity: 1;
}

.datetime-clear:hover {
  background: rgba(239, 68, 68, 0.3);
}

</style>