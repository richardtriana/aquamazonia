<template>
    <span v-if="count > 0" class="badge-notification">
        <i class="fas fa-bell"></i> {{ count }}
    </span>
</template>

<script>
export default {
    props: ["type"], // feeding_prelogs | water_prelogs | water_advanced_prelogs
    data() {
        return {
            counters: {},
            interval: null,
        };
    },
    computed: {
        count() {
            return this.counters[this.type] ?? 0;
        }
    },
    created() {
        this.loadCounters();

        // ⏱️ Refrescar cada 60 segundos
        this.interval = setInterval(() => {
            this.loadCounters();
        }, 60000); // 60,000 ms = 1 minuto
    },
    beforeDestroy() {
        if (this.interval) clearInterval(this.interval);
    },
    methods: {
        loadCounters() {
            axios.get("/api/notifications/counters")
                .then(res => {
                    this.counters = res.data;
                })
                .catch(() => {
                    // en caso de error no crashea el menú
                });
        }
    }
};
</script>

<style>
.badge-notification {
    color: #ffcc00;
    margin-left: 6px;
    font-weight: bold;
    font-size: 0.9rem;
}
</style>
