<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
</script>

<template>

    <Head title="Manual" />
    <div class="p-6 bg-gray-50 min-h-screen font-sans text-sm">
        <div class="max-w-6xl mx-auto">
            <div class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200">
                <div class="bg-teal-600 text-white flex items-center justify-between px-6 py-4 shadow-inner">
                    <span class="font-bold text-xl">Manual Aplikasi</span>
                    <a href="https://drive.google.com/drive/folders/1Ue_KtdT10uRqHs1ozSvSof2adBSv1196" target="_blank"
                        rel="noopener noreferrer"
                        class="text-xs bg-white text-teal-700 hover:bg-teal-50 px-3 py-1.5 rounded-lg font-medium transition inline-flex items-center gap-1.5 shadow-sm">
                        Buka di Google Drive
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>
                <div class="p-0">
                    <iframe src="https://drive.google.com/embeddedfolderview?id=1Ue_KtdT10uRqHs1ozSvSof2adBSv1196#list"
                        class="w-full border-0" style="height: 80vh; min-height: 600px;" allow="autoplay">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</template>
