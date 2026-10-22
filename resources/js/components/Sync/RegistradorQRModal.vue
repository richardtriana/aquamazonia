<template>
    <div class="qr-modal-backdrop" v-if="visible" @click.self="close">
        <div class="qr-modal-card">
            <div class="qr-modal-header">
                <h5 class="mb-0">
                    <i class="fas fa-qrcode mr-2"></i>
                    QR acceso registrador
                </h5>
                <button type="button" class="close" @click="close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="qr-modal-body">
                <p class="qr-modal-text">
                    Escanea este código QR para abrir la pantalla del
                    <strong>registrador</strong> en el dispositivo móvil.
                </p>

                <div class="qr-preview-wrapper">
                    <div class="qr-preview-inner" v-if="qrDataUrl">
                        <img
                            :src="qrDataUrl"
                            alt="QR acceso registrador"
                            class="qr-image"
                        >
                    </div>
                    <div class="qr-loading" v-else>
                        Generando código QR...
                    </div>
                </div>

                <div class="qr-link-box">
                    <div class="qr-link-label">
                        Enlace:
                    </div>
                    <div class="qr-link-value">
                        {{ link }}
                    </div>
                </div>
            </div>

            <div class="qr-modal-footer">
                <button
                    type="button"
                    class="btn btn-light border mr-2"
                    @click="copyLink"
                >
                    <i class="fas fa-link mr-1"></i>
                    Copiar enlace
                </button>

                <button
                    type="button"
                    class="btn btn-success"
                    :disabled="!qrDataUrl"
                    @click="downloadQr"
                >
                    <i class="fas fa-download mr-1"></i>
                    Descargar QR (.png)
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import QRCode from "qrcode";

export default {
    name: "RegistradorQRModal",
    props: {
        visible: {
            type: Boolean,
            default: false
        },
        link: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            qrDataUrl: null
        };
    },
    watch: {
        visible(val) {
            if (val) {
                this.generateQr();
            }
        },
        link() {
            if (this.visible) {
                this.generateQr();
            }
        }
    },
    methods: {
        close() {
            this.$emit("close");
        },
        async generateQr() {
            if (!this.link) {
                this.qrDataUrl = null;
                return;
            }
            try {
                this.qrDataUrl = await QRCode.toDataURL(this.link, {
                    errorCorrectionLevel: "M",
                    margin: 1,
                    width: 260
                });
            } catch (e) {
                console.error("Error generando QR:", e);
                this.qrDataUrl = null;
            }
        },
        downloadQr() {
            if (!this.qrDataUrl) return;
            const a = document.createElement("a");
            a.href = this.qrDataUrl;
            a.download = "qr-registrador.png";
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        },
        copyLink() {
            if (!this.link) return;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(this.link).then(
                    () => {
                        if (window.Swal) {
                            Swal.fire({
                                icon: "success",
                                title: "Copiado",
                                text: "El enlace del registrador se copió al portapapeles.",
                                timer: 1300,
                                showConfirmButton: false
                            });
                        }
                    },
                    () => {
                        this.fallbackCopy();
                    }
                );
            } else {
                this.fallbackCopy();
            }
        },
        fallbackCopy() {
            const textarea = document.createElement("textarea");
            textarea.value = this.link;
            textarea.style.position = "fixed";
            textarea.style.left = "-9999px";
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand("copy");
                if (window.Swal) {
                    Swal.fire({
                        icon: "success",
                        title: "Copiado",
                        text: "El enlace del registrador se copió al portapapeles.",
                        timer: 1300,
                        showConfirmButton: false
                    });
                }
            } catch (e) {
                console.error("No se pudo copiar el enlace", e);
            } finally {
                document.body.removeChild(textarea);
            }
        }
    },
    mounted() {
        if (this.visible) {
            this.generateQr();
        }
    }
};
</script>

<style scoped>
.qr-modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 2100;
    background: rgba(15, 23, 42, 0.65);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.qr-modal-card {
    background: #ffffff;
    border-radius: 18px;
    max-width: 480px;
    width: 100%;
    box-shadow: 0 15px 40px rgba(15, 23, 42, 0.35);
    display: flex;
    flex-direction: column;
}

.qr-modal-header {
    padding: 0.75rem 1.25rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.qr-modal-body {
    padding: 1.25rem 1.25rem 0.75rem 1.25rem;
}

.qr-modal-footer {
    padding: 0.75rem 1.25rem 1rem 1.25rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    flex-wrap: wrap;
}

.qr-modal-text {
    font-size: 0.9rem;
    color: #4b5563;
}

.qr-preview-wrapper {
    margin: 0.75rem auto 1rem auto;
    display: flex;
    justify-content: center;
}

.qr-preview-inner {
    padding: 0.75rem;
    border-radius: 16px;
    background: radial-gradient(circle at top, #e0f2fe, #eff6ff);
    border: 1px solid #dbeafe;
}

.qr-image {
    display: block;
    max-width: 240px;
    width: 100%;
    height: auto;
}

.qr-loading {
    font-size: 0.85rem;
    color: #6b7280;
    padding: 1.5rem 0;
}

.qr-link-box {
    background: #f9fafb;
    border-radius: 10px;
    padding: 0.5rem 0.75rem;
    border: 1px dashed #e5e7eb;
    font-size: 0.82rem;
}

.qr-link-label {
    font-weight: 600;
    color: #6b7280;
    margin-bottom: 0.2rem;
}

.qr-link-value {
    word-break: break-all;
    color: #111827;
}
</style>
