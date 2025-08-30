<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

// State untuk menyimpan partikel yang aktif
const particles = ref([]);
let particleCount = 0;

// Fungsi untuk memicu partikel
const triggerParticles = (event) => {
    // Ambil koordinat
    const x = event.clientX;
    const y = event.clientY;

    // Buat partikel baru
    const newParticle = {
        id: particleCount++,
        x: x,
        y: y,
    };

    particles.value.push(newParticle);

    // Hapus partikel setelah 1.5 detik
    setTimeout(() => {
        particles.value = particles.value.filter(p => p.id !== newParticle.id);
    }, 1500);
};

const handleTouchMove = (event) => {
    if (event.touches && event.touches.length > 0) {
        triggerParticles(event.touches[0]);
    }
};

</script>

<template>
    <AppLayout title="Dashboard">
        <div class="bg-black py-12 min-h-screen relative overflow-hidden" 
             @mousemove="triggerParticles"
             @touchmove="handleTouchMove">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="text-center text-white py-20 relative z-10">
                    <h1 class="text-5xl font-extrabold tracking-tight">
                        Selamat Datang
                    </h1>
                    <p class="mt-4 text-lg text-gray-400">
                        Gerakkan kursor Anda di layar untuk melihat efeknya!
                    </p>
                </div>
            </div>

            <div v-for="particle in particles" :key="particle.id"
                 class="particle absolute rounded-full transition-all duration-[1.5s] ease-out-quad"
                 :style="{ 
                    top: `${particle.y}px`, 
                    left: `${particle.x}px`,
                    '--tw-bg-opacity': Math.random() * 0.5 + 0.5,
                    '--tw-w-h': `${Math.random() * 8 + 4}px`
                 }">
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Transisi Partikel */
.particle {
    width: var(--tw-w-h);
    height: var(--tw-w-h);
    background-color: rgb(255 255 255 / var(--tw-bg-opacity));
    transform: translate(-50%, -50%);
    box-shadow: 0 0 10px 5px #fff,
                0 0 20px 10px #0ff,
                0 0 30px 15px #f0f;
    opacity: 0;
    pointer-events: none;
    animation: fade-out 1.5s forwards;
}

/* Kustomisasi easing untuk transisi yang lebih halus */
.ease-out-quad {
    transition-timing-function: cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

/* Keyframes untuk animasi fade out */
@keyframes fade-out {
    0% {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }
    100% {
        opacity: 0;
        transform: translate(-50%, -50%) scale(0);
    }
}
</style>