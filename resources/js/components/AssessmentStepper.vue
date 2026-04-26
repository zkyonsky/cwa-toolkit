<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Pencil, Building, Banknote, LineChart, BarChart,
    CreditCard, Calculator, Star, ClipboardList, Check
} from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps<{
    assessmentId: number;
    currentStep: number; // 1-indexed
}>();

const steps = [
    {
        number: 1,
        label: 'Data Pemda',
        icon: Pencil,
        href: (id: number) => `/assessments/${id}/edit`,
        color: 'bg-slate-500',
        activeColor: 'bg-slate-700',
        lightColor: 'bg-slate-100 text-slate-700',
    },
    {
        number: 2,
        label: 'Infrastruktur',
        icon: Building,
        href: (id: number) => `/assessment-details/${id}/infrastructure`,
        color: 'bg-teal-600',
        activeColor: 'bg-teal-700',
        lightColor: 'bg-teal-100 text-teal-700',
    },
    {
        number: 3,
        label: 'Data Keuangan',
        icon: Banknote,
        href: (id: number) => `/assessment-details/${id}/budget-real`,
        color: 'bg-green-600',
        activeColor: 'bg-green-700',
        lightColor: 'bg-green-100 text-green-700',
    },
    {
        number: 4,
        label: 'Kondisi Ekonomi',
        icon: LineChart,
        href: (id: number) => `/assessment-details/${id}/economy-condition`,
        color: 'bg-blue-600',
        activeColor: 'bg-blue-700',
        lightColor: 'bg-blue-100 text-blue-700',
    },
    {
        number: 5,
        label: 'Kondisi Keuangan',
        icon: BarChart,
        href: (id: number) => `/assessment-details/${id}/financial-condition`,
        color: 'bg-indigo-600',
        activeColor: 'bg-indigo-700',
        lightColor: 'bg-indigo-100 text-indigo-700',
    },
    {
        number: 6,
        label: 'Debt Service',
        icon: CreditCard,
        href: (id: number) => `/assessment-details/${id}/debt-service`,
        color: 'bg-purple-600',
        activeColor: 'bg-purple-700',
        lightColor: 'bg-purple-100 text-purple-700',
    },
    {
        number: 7,
        label: 'DSCR',
        icon: Calculator,
        href: (id: number) => `/assessment-details/${id}/dscr`,
        color: 'bg-orange-600',
        activeColor: 'bg-orange-700',
        lightColor: 'bg-orange-100 text-orange-700',
    },
    {
        number: 8,
        label: 'Indikasi Rating',
        icon: Star,
        href: (id: number) => `/assessment-details/${id}/indicative-rating`,
        color: 'bg-amber-500',
        activeColor: 'bg-amber-600',
        lightColor: 'bg-amber-100 text-amber-700',
    },
    {
        number: 9,
        label: 'Rencana Aksi',
        icon: ClipboardList,
        href: (id: number) => `/assessment-details/${id}/action-plan`,
        color: 'bg-rose-600',
        activeColor: 'bg-rose-700',
        lightColor: 'bg-rose-100 text-rose-700',
    },
];

const prevStep = computed(() => steps.find(s => s.number === props.currentStep - 1));
const nextStep = computed(() => steps.find(s => s.number === props.currentStep + 1));
</script>

<template>
    <!-- Step Progress Bar -->
    <div class="bg-white border-b border-gray-200 px-4 py-3 shadow-sm">
        <div class="max-w-[1400px] mx-auto">
            <!-- Step counter label -->
            <div class="text-xs text-gray-400 mb-2 font-medium tracking-wider uppercase">
                Langkah {{ currentStep }} dari {{ steps.length }}
            </div>

            <!-- Steps row -->
            <div class="flex items-center gap-0 overflow-x-auto pb-1 scrollbar-none">
                <template v-for="(step, index) in steps" :key="step.number">
                    <!-- Step item -->
                    <Link :href="step.href(assessmentId)" class="flex-shrink-0">
                        <div class="flex items-center gap-1.5 group"
                            :class="[
                                'px-2 py-1.5 rounded-lg transition-all duration-200',
                                step.number === currentStep ? 'bg-gray-100 shadow-sm' : 'hover:bg-gray-50'
                            ]">
                            <!-- Step circle -->
                            <div class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold shadow-sm transition-all"
                                :class="[
                                    step.number < currentStep ? step.color + ' opacity-70' :
                                    step.number === currentStep ? step.color + ' ring-2 ring-offset-1 ring-current shadow-md' :
                                    'bg-gray-200 text-gray-400'
                                ]">
                                <component v-if="step.number > currentStep" :is="step.icon" :size="13" />
                                <Check v-else-if="step.number < currentStep" :size="13" />
                                <span v-else class="text-[11px]">{{ step.number }}</span>
                            </div>
                            <!-- Step label -->
                            <span class="text-[11px] font-medium leading-tight whitespace-nowrap"
                                :class="[
                                    step.number === currentStep ? 'text-gray-900 font-semibold' :
                                    step.number < currentStep ? 'text-gray-500' : 'text-gray-400'
                                ]">
                                {{ step.label }}
                            </span>
                        </div>
                    </Link>

                    <!-- Connector line between steps -->
                    <div v-if="index < steps.length - 1" class="flex-shrink-0 w-4 h-px mx-0.5"
                        :class="step.number < currentStep ? 'bg-gray-400' : 'bg-gray-200'">
                    </div>
                </template>
            </div>

            <!-- Prev / Next navigation -->
            <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-100">
                <Link v-if="prevStep" :href="prevStep.href(assessmentId)"
                    class="flex items-center gap-1.5 text-xs text-gray-500 hover:text-gray-700 transition-colors group">
                    <span class="group-hover:-translate-x-0.5 transition-transform">←</span>
                    <span>{{ prevStep.label }}</span>
                </Link>
                <span v-else class="text-xs text-gray-300">—</span>

                <Link v-if="nextStep" :href="nextStep.href(assessmentId)"
                    class="flex items-center gap-1.5 text-xs font-medium hover:opacity-80 transition-all group"
                    :class="nextStep.lightColor">
                    <span class="px-3 py-1 rounded-full flex items-center gap-1.5" :class="nextStep.lightColor">
                        Lanjut: {{ nextStep.label }}
                        <span class="group-hover:translate-x-0.5 transition-transform">→</span>
                    </span>
                </Link>
                <span v-else class="text-xs font-medium text-emerald-600 flex items-center gap-1">
                    <Check :size="13" /> Semua langkah selesai
                </span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.scrollbar-none::-webkit-scrollbar { display: none; }
.scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
</style>
