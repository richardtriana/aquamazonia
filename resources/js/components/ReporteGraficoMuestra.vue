<template>
  <div class="container-fluid py-3">
    <div class="row justify-content-center">
      <div class="col-12">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-white border-0 pb-0">
            <h5 class="mb-0 text-center text-primary fw-semibold">
              REPORTE GRÁFICO DE SIEMBRAS (EXISTENCIAS)
            </h5>
          </div>

          <div class="card-body position-relative">

            <!-- SPINNER GLOBAL AQUAMAZONIA -->
            <div v-if="isLoading" class="global-spinner">
              <div class="global-spinner-inner">
                <div class="global-spinner-ring"></div>
                <div class="global-spinner-logo">
                  AQUAMAZONIA
                </div>
                <div class="global-spinner-text">
                  Cargando información, por favor espera...
                </div>
              </div>
            </div>

            <!-- FILTROS -->
            <div class="row g-3 mb-3 align-items-end">
              <div class="col-md-3">
                <label class="form-label mb-1">Estado:</label>
                <select v-model="f_estado" class="form-select">
                  <option value="1">Activa</option>
                  <option value="0">Inactiva</option>
                  <option value="-1">Todas</option>
                </select>
              </div>

              <div class="col-md-3">
                <label class="form-label mb-1">Siembra:</label>
                <select v-model="f_siembra" class="form-select">
                  <option value="-1">Todas</option>
                  <option
                    v-for="s in listadoSiembras"
                    :key="s.id"
                    :value="s.id"
                  >
                    {{ s.nombre_siembra }}
                  </option>
                </select>
              </div>

              <div class="col-md-3">
                <label class="form-label mb-1">Estanque:</label>
                <select v-model="id_contenedor" class="form-select">
                  <option value="-1">Todos</option>
                  <option
                    v-for="c in listadoEstanques"
                    :key="c.id"
                    :value="c.id"
                  >
                    {{ c.contenedor }}
                  </option>
                </select>
              </div>

              <div class="col-md-3">
                <label class="form-label mb-1">Tipo de siembra:</label>
                <select v-model="type" class="form-select">
                  <option value="">Todas</option>
                  <option value="Monocultivo">Monocultivo</option>
                  <option value="Policultivo">Policultivo</option>
                </select>
              </div>

              <div class="col-md-3">
                <label class="form-label mb-1">Fecha inicio desde:</label>
                <input
                  v-model="f_inicio_d"
                  type="date"
                  class="form-control"
                  placeholder="dd/mm/aaaa"
                />
              </div>

              <div class="col-md-3">
                <label class="form-label mb-1">Fecha inicio hasta:</label>
                <input
                  v-model="f_inicio_h"
                  type="date"
                  class="form-control"
                  placeholder="dd/mm/aaaa"
                />
              </div>

              <div class="col-md-3">
                <label class="form-label mb-1">
                  Mostrar {{ nro_results }} por página
                </label>
                <input
                  v-model.number="nro_results"
                  type="number"
                  min="5"
                  class="form-control"
                />
              </div>

              <div class="col-md-3 d-flex gap-2">
                <div class="flex-grow-1">
                  <button
                    type="button"
                    class="btn btn-primary w-100"
                    @click="listarExistencias()"
                  >
                    <i class="fas fa-search me-1"></i>
                    Buscar
                  </button>
                </div>
                <div>
                  <downloadexcel
                    class="btn btn-success"
                    :fetch="fetchExcelData"
                    :fields="json_fields"
                    name="reporte-grafico-siembras.xls"
                    type="xls"
                  >
                    <i class="fa fa-fw fa-download"></i>
                  </downloadexcel>
                </div>
              </div>
            </div>

            <!-- RESÚMENES -->
            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <div class="resume-card">
                  <div class="resume-label">Total siembras</div>
                  <div class="resume-value">
                    {{ resumen.totalSiembras | numeral("0,0") }}
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="resume-card">
                  <div class="resume-label">Biomasa disponible total (kg)</div>
                  <div class="resume-value">
                    {{ resumen.biomasaTotal | numeral("0,0.00") }}
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="resume-card">
                  <div class="resume-label">Animales actuales</div>
                  <div class="resume-value">
                    {{ resumen.animalesActuales | numeral("0,0.00") }}
                  </div>
                </div>
              </div>
            </div>

            <!-- SELECTOR VARIABLE + GRÁFICA -->
            <div class="row mb-2 align-items-center">
              <div class="col-md-6 d-flex align-items-center">
                <span class="me-2 fw-semibold">Variable a graficar:</span>
                <select
                  v-model="selectedVariable"
                  class="form-select w-auto"
                  @change="updateChart"
                >
                  <option
                    v-for="opt in chartVariables"
                    :key="opt.value"
                    :value="opt.value"
                  >
                    {{ opt.label }}
                  </option>
                </select>
              </div>
            </div>

            <div class="chart-wrapper mb-3">
              <canvas ref="chartCanvas"></canvas>
            </div>

            <!-- TABLA -->
            <div class="table-responsive table-container">
              <table class="table table-sm table-hover table-bordered align-middle">
                <thead class="thead-primary">
                  <tr>
                    <th>#</th>
                    <th>Siembra</th>
                    <th>Estanque</th>
                    <th>Capacidad (m³)</th>
                    <th>Fecha inicio</th>
                    <th>Tipo</th>
                    <th class="text-end">Peso actual esp (g)</th>
                    <th class="text-end">Cant. actual</th>
                    <th class="text-end">Biomasa disponible (kg)</th>
                    <th class="text-end">Ganancia peso día (g)</th>
                    <th class="text-end">Conv. final</th>
                    <th class="text-end">Costo producción final</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(item, index) in existencias.data"
                    :key="item.id"
                  >
                    <td>{{ index + 1 + offsetIndex }}</td>
                    <td>{{ item.nombre_siembra }}</td>
                    <td>{{ item.id_contenedor }}</td>
                    <td class="text-end">
                      {{ item.capacidad | numeral("0,0") }}
                    </td>
                    <td>{{ item.fecha_inicio }}</td>
                    <td>{{ item.tipo }}</td>
                    <td class="text-end">
                      {{ usePesoActual(item) | numeral("0,0.00") }}
                    </td>
                    <td class="text-end">
                      {{ item.cantidad_actual | numeral("0,0.00") }}
                    </td>
                    <td class="text-end">
                      {{ item.biomasa_disponible | numeral("0,0.00") }}
                    </td>
                    <td class="text-end">
                      {{ item.ganancia_peso_dia | numeral("0,0.00") }}
                    </td>
                    <td class="text-end">
                      {{ item.conversion_final | numeral("0,0.00") }}
                    </td>
                    <td class="text-end">
                      {{ item.costo_produccion_final | numeral("0,0.00") }}
                    </td>
                  </tr>

                  <tr v-if="existencias.data && existencias.data.length" class="fw-semibold">
                    <td colspan="6" class="text-end">Totales página:</td>
                    <td class="text-end">
                      {{ totals.pesoActual | numeral("0,0.00") }}
                    </td>
                    <td class="text-end">
                      {{ totals.cantidadActual | numeral("0,0.00") }}
                    </td>
                    <td class="text-end">
                      {{ totals.biomasaDisponible | numeral("0,0.00") }}
                    </td>
                    <td class="text-end">
                      {{ totals.gananciaPesoDia | numeral("0,0.00") }}
                    </td>
                    <td class="text-end">
                      {{ totals.conversionFinal | numeral("0,0.00") }}
                    </td>
                    <td class="text-end">
                      {{ totals.costoProduccionFinal | numeral("0,0.00") }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- PAGINACIÓN -->
            <pagination
              v-if="existencias && existencias.total"
              :align="'center'"
              :data="existencias"
              :limit="8"
              @pagination-change-page="listarExistencias"
            >
              <span slot="prev-nav">&lt; Anterior</span>
              <span slot="next-nav">Siguiente &gt;</span>
            </pagination>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import downloadexcel from "vue-json-excel";
import Chart from "chart.js";

export default {
  name: "ReporteGraficoMuestra",
  components: {
    downloadexcel,
  },
  data() {
    return {
      isLoading: false,

      // datos
      existencias: {
        data: [],
        current_page: 1,
        per_page: 15,
        total: 0,
      },
      listadoSiembras: [],
      listadoEstanques: [],

      // filtros
      f_estado: "1",
      f_siembra: "-1",
      id_contenedor: "-1",
      f_inicio_d: "",
      f_inicio_h: "",
      type: "",
      nro_results: 15,

      // chart
      chartInstance: null,
      selectedVariable: "peso_actual",
      chartVariables: [
        {
          value: "peso_actual",
          label: "Peso actual por espécimen (g)",
          field: "peso_actual",
        },
        {
          value: "biomasa_disponible",
          label: "Biomasa disponible (kg)",
          field: "biomasa_disponible",
        },
        {
          value: "cantidad_actual",
          label: "Animales actuales",
          field: "cantidad_actual",
        },
        {
          value: "ganancia_peso_dia",
          label: "Ganancia peso día (g)",
          field: "ganancia_peso_dia",
        },
        {
          value: "costo_produccion_final",
          label: "Costo producción final",
          field: "costo_produccion_final",
        },
      ],

      // resumen cards
      resumen: {
        totalSiembras: 0,
        biomasaTotal: 0,
        animalesActuales: 0,
      },

      // campos Excel
      json_fields: {
        Siembra: "nombre_siembra",
        Estanque: "id_contenedor",
        Capacidad: "capacidad",
        "Fecha inicio": "fecha_inicio",
        Tipo: "tipo",
        "Peso actual esp (g)": {
          field: "peso_actual",
          callback: (value, row) => {
            const v = row.peso_actual_esp || row.peso_actual || 0;
            return typeof numeral !== "undefined"
              ? numeral(v).format("0,0.00")
              : v;
          },
        },
        "Cant. actual": {
          field: "cantidad_actual",
          callback: (value) =>
            typeof numeral !== "undefined"
              ? numeral(value).format("0,0.00")
              : value,
        },
        "Biomasa disponible (kg)": {
          field: "biomasa_disponible",
          callback: (value) =>
            typeof numeral !== "undefined"
              ? numeral(value).format("0,0.00")
              : value,
        },
        "Ganancia peso día (g)": {
          field: "ganancia_peso_dia",
          callback: (value) =>
            typeof numeral !== "undefined"
              ? numeral(value).format("0,0.00")
              : value,
        },
        "Conv. final": {
          field: "conversion_final",
          callback: (value) =>
            typeof numeral !== "undefined"
              ? numeral(value).format("0,0.00")
              : value,
        },
        "Costo producción final": {
          field: "costo_produccion_final",
          callback: (value) =>
            typeof numeral !== "undefined"
              ? numeral(value).format("0,0.00")
              : value,
        },
      },
    };
  },
  computed: {
    totals() {
      const rows = this.existencias.data || [];
      const sum = (field, fallbackField = null) =>
        rows.reduce((acc, r) => {
          let v = r[field];
          if ((v === undefined || v === null) && fallbackField) {
            v = r[fallbackField];
          }
          return acc + (parseFloat(v) || 0);
        }, 0);

      return {
        pesoActual: sum("peso_actual", "peso_actual_esp"),
        cantidadActual: sum("cantidad_actual"),
        biomasaDisponible: sum("biomasa_disponible"),
        gananciaPesoDia: sum("ganancia_peso_dia"),
        conversionFinal: sum("conversion_final"),
        costoProduccionFinal: sum("costo_produccion_final"),
      };
    },
    offsetIndex() {
      if (!this.existencias || !this.existencias.per_page) return 0;
      return (this.existencias.current_page - 1) * this.existencias.per_page;
    },
  },
  methods: {
    usePesoActual(item) {
      if (item.peso_actual_esp !== undefined && item.peso_actual_esp !== null) {
        return item.peso_actual_esp;
      }
      return item.peso_actual || 0;
    },

    buildParams(page = 1) {
      return {
        f_siembra: this.f_siembra && this.f_siembra !== "-1" ? this.f_siembra : -1,
        f_contenedor:
          this.id_contenedor && this.id_contenedor !== "-1"
            ? this.id_contenedor
            : -1,
        f_estado:
          this.f_estado && this.f_estado !== "-1" ? this.f_estado : -1,
        f_inicio_d: this.f_inicio_d ? this.f_inicio_d : -1,
        f_inicio_h: this.f_inicio_h ? this.f_inicio_h : -1,
        type: this.type || "",
        nro_results: this.nro_results || 15,
        page: page,
      };
    },

    listarExistencias(page = 1) {
      const params = this.buildParams(page);
      const me = this;

      this.isLoading = true;

      axios
        .get("api/traer-existencias-detalle", { params })
        .then((response) => {
          if (response.data && response.data.existencias) {
            me.existencias = response.data.existencias;
          } else {
            me.existencias = {
              data: [],
              current_page: 1,
              per_page: params.nro_results,
              total: 0,
            };
          }
          me.updateResumen();
          me.updateChart();
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.isLoading = false;
        });
    },

    listarSiembras() {
      const me = this;
      axios
        .get("api/siembras/listado")
        .then((response) => {
          me.listadoSiembras = response.data || [];
        })
        .catch((e) => console.error(e));
    },

    listarEstanques() {
      const me = this;
      axios
        .get("api/contenedores")
        .then((response) => {
          me.listadoEstanques = response.data || [];
        })
        .catch((e) => console.error(e));
    },

    updateResumen() {
      const rows = this.existencias.data || [];
      const sum = (field) =>
        rows.reduce((acc, r) => acc + (parseFloat(r[field]) || 0), 0);

      this.resumen.totalSiembras = this.existencias.total || rows.length;
      this.resumen.biomasaTotal = sum("biomasa_disponible");
      this.resumen.animalesActuales = sum("cantidad_actual");
    },

    updateChart() {
      const rows = this.existencias.data || [];
      const variable = this.chartVariables.find(
        (v) => v.value === this.selectedVariable
      );
      if (!variable) return;

      const labels = rows.map((r) => r.nombre_siembra);
      const dataValues = rows.map((r) => {
        if (variable.field === "peso_actual") {
          return this.usePesoActual(r);
        }
        return parseFloat(r[variable.field]) || 0;
      });

      const ctx = this.$refs.chartCanvas
        ? this.$refs.chartCanvas.getContext("2d")
        : null;
      if (!ctx) return;

      if (this.chartInstance) {
        this.chartInstance.destroy();
      }

      this.chartInstance = new Chart(ctx, {
        type: "bar",
        data: {
          labels,
          datasets: [
            {
              label: variable.label,
              data: dataValues,
              backgroundColor: "rgba(37, 99, 235, 0.25)",
              borderColor: "rgba(37, 99, 235, 0.9)",
              borderWidth: 1.5,
              borderRadius: 8,
              maxBarThickness: 40,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
              labels: {
                font: {
                  size: 12,
                  family: "'Inter', system-ui, -apple-system, BlinkMacSystemFont",
                },
              },
            },
            tooltip: {
              backgroundColor: "rgba(15,23,42,0.95)",
              padding: 10,
              cornerRadius: 8,
              titleFont: {
                size: 12,
              },
              bodyFont: {
                size: 11,
              },
            },
          },
          layout: {
            padding: {
              top: 10,
              right: 16,
              bottom: 10,
              left: 8,
            },
          },
          scales: {
            x: {
              ticks: {
                font: {
                  size: 11,
                },
                maxRotation: 45,
                minRotation: 45,
              },
              grid: {
                display: false,
              },
            },
            y: {
              beginAtZero: true,
              ticks: {
                font: {
                  size: 11,
                },
              },
              grid: {
                color: "rgba(148, 163, 184, 0.35)",
                drawBorder: false,
              },
            },
          },
        },
      });
    },

    fetchExcelData() {
      return this.existencias.data || [];
    },
  },
  mounted() {
    this.listarSiembras();
    this.listarEstanques();
    this.listarExistencias();
  },
  beforeDestroy() {
    if (this.chartInstance) {
      this.chartInstance.destroy();
    }
  },
};
</script>

<style scoped>
.card-header h5 {
  letter-spacing: 0.04em;
}

.resume-card {
  border-radius: 16px;
  background: linear-gradient(
    135deg,
    #f9fafb 0%,
    #edf2ff 40%,
    #e0f2fe 100%
  );
  padding: 0.75rem 1rem;
  border: 1px solid #e5e7eb;
}

.resume-label {
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #6b7280;
  margin-bottom: 0.15rem;
}

.resume-value {
  font-size: 1.2rem;
  font-weight: 600;
  color: #111827;
}

.chart-wrapper {
  width: 100%;
  height: 320px;
  border-radius: 18px;
  border: 1px solid #e5e7eb;
  padding: 0.75rem 1rem 1rem 0.75rem;
  background: radial-gradient(
    circle at top left,
    rgba(191, 219, 254, 0.6),
    #ffffff
  );
}

.table-container {
  margin-top: 1rem;
}

.thead-primary th {
  background: #0f172a;
  color: #f9fafb;
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.table td,
.table th {
  font-size: 0.82rem;
  vertical-align: middle;
}
</style>
