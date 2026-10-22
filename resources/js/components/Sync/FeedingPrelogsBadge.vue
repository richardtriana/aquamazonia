<template>
    <span v-if="count > 0" class="feeding-prelogs-badge">
        <i class="fas fa-bell"></i>
        <span class="badge-count">{{ count }}</span>
    </span>
</template>


<script>
export default {
    data() {
        return {
            count: 0,
            loading: false,
            intervalId: null
        };
    },
    computed: {
        hasPending() {
            return !this.loading && this.count > 0;
        },
        displayCount() {
            if (this.count > 99) return "99+";
            return this.count;
        }
    },
    methods: {
        fetchCount() {
            this.loading = true;

            axios
                .get("api/feeding-prelogs", {
                    params: {
                        no_paginate: 1
                    }
                })
                .then(response => {
                    const data = response.data || [];
                    this.count = Array.isArray(data) ? data.length : 0;
                })
                .catch(error => {
                    console.error("Error cargando preregistros pendientes:", error);
                    // en error no rompemos, solo dejamos el conteo como está
                })
                .finally(() => {
                    this.loading = false;
                });
        }
    },
    mounted() {
        // Primera carga
        this.fetchCount();

        // Refresco periódico (cada 60 segundos)
        this.intervalId = setInterval(() => {
            this.fetchCount();
        }, 60000);

        // Escuchar eventos globales desde otras vistas
        if (this.$root) {
            this.$root.$on("feeding-prelogs-updated", this.fetchCount);
        }
    },
    beforeDestroy() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
        if (this.$root) {
            this.$root.$off("feeding-prelogs-updated", this.fetchCount);
        }
    }
};
</script>

<style scoped>
.feeding-prelogs-badge {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* Campanita igual que antes */
.feeding-prelogs-badge i {
    font-size: 0.95rem;
    color: #f59e0b; /* naranja, por ejemplo */
}

/* 🔴 SOLO NÚMERO ROJO, SIN CÍRCULO */
.badge-count {
    position: absolute;
    top: -6px;
    right: -2px;

    /* sin círculo */
    background: transparent;
    border: none;
    border-radius: 0;

    /* el número en rojo */
    color: #ef4444;
    font-size: 0.75rem;
    font-weight: 700;
    line-height: 1;
}

.feeding-badge-wrapper {
    position: relative;
    display: inline-flex;
    align-items: center;
    margin-left: 0.35rem;
}

/* Campanita */
.feeding-badge-icon {
    font-size: 1rem;
    color: #9ca3af; /* gris */
}

.feeding-badge-icon--active {
    color: #f97316; /* naranja cuando hay pendientes */
}

/* Circulito rojo con número */
.feeding-badge-counter {
    position: absolute;
    top: -0.35rem;
    right: -0.45rem;
    min-width: 18px;
    height: 18px;
    border-radius: 999px;
    background-color: #ef4444;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 4px;
    box-shadow: 0 0 0 2px #ffffff;
}

.feeding-badge-counter-inner {
    color: #ffffff;
    font-size: 0.65rem;
    font-weight: 600;
    line-height: 1;
}
</style>
