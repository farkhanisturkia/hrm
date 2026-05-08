<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';

const videoRef = ref(null);
const canvasRef = ref(null);
const isCameraActive = ref(false);
const attendanceType = ref('check_in');
// State untuk WFO/WFA, di-bind dengan elemen <select>
const workType = ref('wfo');
const recognitionResult = ref(null);
const isProcessing = ref(false);
const currentTime = ref('');
const currentDate = ref('');
const latitude = ref(null);
const longitude = ref(null);
const locationStatus = ref("Belum mengambil lokasi");
let timeInterval;
let scanInterval;

let successAudio;
let errorAudio;
let audioUnlocked = false;

let lastAudioPlay = {
    key: null,
    time: 0
};

const updateTime = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('us-EN', {
        hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
    });
    currentDate.value = now.toLocaleDateString('id-ID', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
    });
};

const unlockAudioBrowserPolicy = () => {
    if (audioUnlocked) return;
    try {
        if (successAudio) {
            successAudio.play().then(() => {
                successAudio.pause();
                successAudio.currentTime = 0;
            }).catch(() => {});
        }
        if (errorAudio) {
            errorAudio.play().then(() => {
                errorAudio.pause();
                errorAudio.currentTime = 0;
            }).catch(() => {});
        }
        audioUnlocked = true;
    } catch (e) {
        console.log("Gagal unlock audio", e);
    }
};

const getLocation = () => {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            locationStatus.value = "Geolocation tidak didukung"
            reject()
        }

        locationStatus.value = "Mengambil lokasi..."

        navigator.geolocation.getCurrentPosition(
            (position) => {
                latitude.value = position.coords.latitude
                longitude.value = position.coords.longitude
                locationStatus.value = "Lokasi berhasil didapatkan"
                resolve()
            },
            (error) => {
                locationStatus.value = "Gagal mengambil lokasi"
                reject(error)
            },
            {
                enableHighAccuracy: true,
                timeout: 10000
            }
        )
    })
}

const toggleCamera = async () => {
    unlockAudioBrowserPolicy();

    if (isCameraActive.value) stopCamera();
    else await startCamera();
};

const startCamera = async () => {
    await getLocation();

    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
        if (videoRef.value) {
            videoRef.value.srcObject = stream;
            isCameraActive.value = true;
        }
    } catch (error) {
        alert("Gagal akses kamera. Pastikan izin browser aktif.");
    }
};

const stopCamera = () => {
    if (videoRef.value && videoRef.value.srcObject) {
        videoRef.value.srcObject.getTracks().forEach(t => t.stop());
        videoRef.value.srcObject = null;
    }
    isCameraActive.value = false;
};

const playSound = (type, uniqueKey) => {
    const now = Date.now();
    
    // Cek apakah key (nama/error) sama DAN belum lewat 10 detik (10.000 ms)
    if (lastAudioPlay.key === uniqueKey && (now - lastAudioPlay.time) < 10000) {
        return; // Hentikan fungsi di sini (suara tidak diputar / cooldown aktif)
    }

    try {
        if (type === 'success' && successAudio) {
            successAudio.currentTime = 0;
            const playPromise = successAudio.play();
            if (playPromise !== undefined) {
                playPromise.catch(e => console.warn('Audio success dicegah browser:', e));
            }
        } else if (type === 'error' && errorAudio) {
            errorAudio.currentTime = 0;
            const playPromise = errorAudio.play();
            if (playPromise !== undefined) {
                playPromise.catch(e => console.warn('Audio error dicegah browser:', e));
            }
        }

        // Simpan data pemutaran ini sebagai data terakhir
        lastAudioPlay.key = uniqueKey;
        lastAudioPlay.time = now;

    } catch (err) {
        console.error('Gagal memutar suara:', err);
    }
};

const captureAndRecognize = async () => {
    if (!isCameraActive.value || isProcessing.value || !videoRef.value) return;

    isProcessing.value = true;

    try {
        const context = canvasRef.value.getContext('2d');
        const scaleWidth = 500;
        const scaleHeight = (videoRef.value.videoHeight / videoRef.value.videoWidth) * scaleWidth;
        
        canvasRef.value.width = scaleWidth;
        canvasRef.value.height = scaleHeight;
        context.drawImage(videoRef.value, 0, 0, scaleWidth, scaleHeight);
        
        const imageBase64 = canvasRef.value.toDataURL('image/jpeg');
        const response = await axios.post('/attendance/store', {
            image: imageBase64,
            type: attendanceType.value,
            latitude: latitude.value,
            longitude: longitude.value,
            work_type: workType.value,
        });

        const res = response.data;
        const isSuccess = res.status === 'success';

        recognitionResult.value = {
            status: isSuccess ? 'success' : 'error',
            name: res.name || "Gagal",
            message: res.message,
            workType: res.work_type || 'WFO',
            type: attendanceType.value
        }

        const identityKey = `${isSuccess ? 'success' : 'error'}-${res.name || 'Gagal'}`;

        if (isSuccess) {
            playSound('success', identityKey);
        } else {
            playSound('error', identityKey);
        }

    } catch (err) {
        let errMessage = 'Unknown Error';
        
        // Menangani error dari backend 
        if (err.response && err.response.data && err.response.data.message) {
            errMessage = err.response.data.message;
        }

        recognitionResult.value = {
            status: 'error',
            name: 'Peringatan',
            message: errMessage,
            workType: 'WFO',
            type: attendanceType.value
        }
        
        const errorKey = `error-${errMessage}`;
        playSound('error', errorKey);
        
    } finally {
        isProcessing.value = false;
    }
};

onMounted(() => {
    successAudio = new Audio('/backsounds/success.mp3');
    errorAudio = new Audio('/backsounds/error.mp3');

    updateTime();
    timeInterval = setInterval(updateTime, 1000);
    scanInterval = setInterval(() => {
        if (isCameraActive.value) captureAndRecognize();
    }, 1500); 
});

onBeforeUnmount(() => {
    stopCamera();
    clearInterval(timeInterval);
    clearInterval(scanInterval);
});
</script>

<template>
    <Head title="Absensi Wajah" />

    <div class="min-h-screen bg-gray-100 font-sans text-gray-900 flex flex-col">
        
        <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                
                <Link href="/" class="group flex items-center gap-3 text-gray-500 hover:text-indigo-600 transition-colors">
                    <div class="p-2 bg-gray-50 rounded-lg group-hover:bg-indigo-50 border border-gray-200 group-hover:border-indigo-100 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </div>
                    <div>
                        <span class="block text-sm font-bold text-gray-800 group-hover:text-indigo-700">Kembali</span>
                        <span class="block text-[10px] text-gray-400">Ke Halaman Utama</span>
                    </div>
                </Link>

                <div class="text-right">
                    <div class="text-2xl font-mono font-bold text-gray-800 leading-none">{{ currentTime }}</div>
                    <div class="text-sm text-gray-500 font-medium uppercase mt-1">{{ currentDate }}</div>
                </div>
            </div>
        </header>

        <main class="flex-grow flex items-center justify-center p-6">
            <div class="w-full max-w-6xl">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:h-[600px]">
                    
                    <div class="flex flex-col gap-4 h-full">

                        <div class="bg-white p-3 rounded-2xl shadow-sm border border-gray-200 flex flex-col gap-1">
                            <label for="work-type" class="text-xs font-bold text-gray-500 ml-1">TIPE ABSENSI</label>
                            <div class="relative w-full">
                                <select 
                                    id="work-type" 
                                    v-model="workType" 
                                    @change="unlockAudioBrowserPolicy"
                                    class="w-full appearance-none bg-gray-50 border border-gray-200 text-gray-700 font-semibold text-sm rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                                >
                                    <option value="wfo">Kerja di Kantor (WFO)</option>
                                    <option value="wfa">Kerja dari Luar (WFA)</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-2 rounded-2xl shadow-sm border border-gray-200 flex">
                            <button @click="attendanceType = 'check_in'; unlockAudioBrowserPolicy();"
                                class="flex-1 py-3 rounded-xl text-sm font-bold transition-all duration-200 flex items-center justify-center gap-2"
                                :class="attendanceType === 'check_in' ? 'bg-emerald-600 text-white shadow-md transform scale-[1.02]' : 'text-gray-500 hover:bg-gray-50'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd" /></svg>
                                CHECK IN
                            </button>
                            <button @click="attendanceType = 'check_out'; unlockAudioBrowserPolicy();"
                                class="flex-1 py-3 rounded-xl text-sm font-bold transition-all duration-200 flex items-center justify-center gap-2"
                                :class="attendanceType === 'check_out' ? 'bg-rose-600 text-white shadow-md transform scale-[1.02]' : 'text-gray-500 hover:bg-gray-50'">
                                CHECK OUT
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1.293-9.293a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L10 5.586 8.707 4.293a1 1 0 00-1.414 1.414l3 3zM10 14a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" /><path d="M10 12a1 1 0 100 2 1 1 0 000-2z" /></svg>
                            </button>
                        </div>

                        <div class="flex-grow min-h-[400px] lg:min-h-0 bg-slate-900 rounded-3xl shadow-lg border border-gray-200 overflow-hidden relative group flex flex-col items-center justify-center isolate">
                            <video v-show="isCameraActive" ref="videoRef" autoplay playsinline muted 
                                class="absolute inset-0 w-full h-full object-cover transform scale-x-[-1]"></video>

                            <div v-if="isCameraActive && !recognitionResult" class="absolute inset-0 z-10 pointer-events-none flex items-center justify-center">
                                <div class="w-64 h-64 border-2 border-dashed border-white/70 rounded-full animate-spin-slow opacity-60"></div>
                                <div class="absolute w-56 h-56 border-2 border-white/40 rounded-3xl"></div>
                            </div>

                            <div v-if="!isCameraActive" class="z-20 text-center">
                                <button @click="toggleCamera" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-full shadow-lg transition transform hover:scale-105 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" /></svg>
                                    Nyalakan Kamera
                                </button>
                            </div>

                            <button 
                                v-if="isCameraActive"
                                @click="toggleCamera"
                                class="absolute top-4 right-4 z-50 bg-white/10 backdrop-blur-md border border-white/20 text-white hover:bg-red-500/80 hover:border-red-500 px-4 py-2 rounded-full text-sm font-bold shadow-lg transition-all flex items-center gap-2 group"
                            >
                                <span class="w-2 h-2 rounded-full bg-red-500 group-hover:bg-white transition-colors animate-pulse"></span>
                                Matikan
                            </button>
                            
                            <div v-if="isCameraActive" class="absolute top-4 left-4 z-20 bg-black/40 backdrop-blur px-3 py-1.5 rounded-full flex items-center gap-2 border border-white/10">
                                <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                                <span class="text-[10px] font-bold text-white uppercase tracking-wider">Live View</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 flex flex-col items-center justify-center p-8 text-center relative overflow-hidden min-h-[300px] lg:min-h-0">
                        <div class="absolute top-0 w-full h-2 bg-gradient-to-r from-indigo-500 to-purple-600"></div>
                        
                        <transition mode="out-in" enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-4">
                            
                            <div v-if="!recognitionResult" key="empty" class="flex flex-col items-center gap-6 opacity-60">
                                <div class="w-32 h-32 bg-gray-50 rounded-full flex items-center justify-center border-4 border-gray-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-800">Menunggu Wajah</h3>
                                    <p class="text-gray-500 text-sm">Pastikan wajah terlihat jelas di kamera.</p>
                                </div>
                            </div>

                            <div v-else key="result" class="flex flex-col items-center gap-6 w-full">
                                <div class="w-36 h-36 rounded-full flex items-center justify-center shadow-lg transform transition-all duration-500"
                                    :class="recognitionResult.status === 'success' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'">
                                    
                                    <svg v-if="recognitionResult.status === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-20 w-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-20 w-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>

                                <div class="w-full">
                                    <h2 class="text-3xl md:text-5xl font-black text-gray-900 mb-3 tracking-tight">{{ recognitionResult.name }}</h2>
                                    
                                    <div class="inline-flex items-center px-6 py-3 rounded-2xl text-lg font-bold border shadow-sm"
                                        :class="recognitionResult.status === 'success' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'">
                                        {{ recognitionResult.message }}
                                    </div>
                                    
                                    <p class="text-sm text-gray-400 mt-6 uppercase tracking-widest font-semibold">
                                        Status Terakhir • {{ recognitionResult.type === 'check_in' ? 'Masuk' : 'Pulang' }} ({{ recognitionResult.workType.toUpperCase() }})
                                    </p>
                                </div>
                            </div>

                        </transition>
                    </div>
                </div>
            </div>
        </main>
        
        <canvas ref="canvasRef" class="hidden"></canvas>
    </div>
</template>

<style scoped>
.animate-spin-slow { animation: spin 8s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>