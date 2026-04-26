<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ChevronLeft, Save, Plus, Trash, HelpCircle } from '@lucide/vue';
import { ref, onMounted } from 'vue';
import AssessmentStepper from '@/components/AssessmentStepper.vue';
import { can } from '@/lib/can';
import { HoverCard, HoverCardContent, HoverCardTrigger } from "@/components/ui/hover-card"

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Edit Infrastructure Assessment',
                href: '#',
            },
        ],
    },
});

const props = defineProps<{
    assessment: { id: number, date: string, info: string, result: string },
    infrasService: any,
    infrasPriorities: any[],
    infrasConclusion: any,
    indicators: any,
    year1: number,
    year2: number
}>();

const year1 = props.year1;
const year2 = props.year2;

const getIndicator = (year: number, field: string) => {
    const val = props.indicators[year]?.[field];
    return val !== undefined && val !== null ? val : '-';
};

const sectors = [
    { key: 'education', label: 'Layanan Dasar Pendidikan' },
    { key: 'health', label: 'Layanan Dasar Kesehatan' },
    { key: 'water', label: 'Penyediaan Air minum layak' },
    { key: 'waste', label: 'Pengelolaan Limbah dan Sampah' },
    { key: 'road', label: 'Jalan & Jalan Tol' },
    { key: 'it', label: 'Infrastruktur Teknologi dan Komunikasi' },
    { key: 'agriculture', label: 'Sektor Pertanian (Sumber daya air dan irigasi)' },
    { key: 'transport', label: 'Transportasi (Bandara, Pelabuhan, Kereta Api, Angkutan Publik)' },
    { key: 'tourism', label: 'Sektor Pariwisata' },
    { key: 'sport_art_culture', label: 'Sektor Olahraga, kesenian, dan budaya' },
    { key: 'electricity', label: 'Ketenagalistrikan' },
    { key: 'food', label: 'Ketahanan Pangan' },
    { key: 'commerce', label: 'Perdagangan (Kawasan industri, pasar, dsb.)' },
];

const generateInitialServices = () => {
    let s = {} as any;
    sectors.forEach(sector => {
        // If DB has the object already via casts, use it, otherwise use fallback
        const existingInfo = props.infrasService[sector.key] || { status: '', desc: '' };
        s[sector.key] = {
            status: typeof existingInfo === 'string' ? '' : existingInfo.status || '',
            desc: typeof existingInfo === 'string' ? existingInfo : existingInfo.desc || ''
        };
    });
    return s;
};

const getDefaultPriorityRow = () => ({
    plan: '',
    exp_outcome: '',
    rank: 1,
    estimated_cost: 0,
    fund_source: '',
    alt_fund_need: 0,
    alt_fund_source: ''
});

const form = useForm({
    services: generateInitialServices(),
    priorities: props.infrasPriorities && props.infrasPriorities.length > 0
        ? [...props.infrasPriorities]
        : [getDefaultPriorityRow()],
    conclusion: {
        advantage: props.infrasConclusion?.advantage || '',
        challenge: props.infrasConclusion?.challenge || ''
    },
    indicators: {
        [year1]: {
            infras_real: props.indicators[year1]?.infras_real ?? 0,
            fiscal_ratio: props.indicators[year1]?.fiscal_ratio ?? 0,
        },
        [year2]: {
            infras_real: props.indicators[year2]?.infras_real ?? 0,
            fiscal_ratio: props.indicators[year2]?.fiscal_ratio ?? 0,
        }
    }
});

const submit = () => {
    form.put(`/assessment-details/${props.assessment.id}/infrastructure`);
};

const addPriority = () => {
    form.priorities.push(getDefaultPriorityRow());
};

const removePriority = (index: number) => {
    form.priorities.splice(index, 1);
};
</script>

<template>

    <Head title="Assessment Infrastruktur" />



    <AssessmentStepper :assessment-id="assessment.id" :current-step="2" />

    <div class="p-6 bg-gray-50 min-h-screen font-sans">
        <div class="max-w-[1200px] mx-auto">
            <div class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200">
                <form @submit.prevent="submit">

                    <!-- HEADER -->
                    <div class="bg-teal-600 text-white text-center font-bold text-xl py-4 shadow-inner">
                        <div class="flex items-center justify-center gap-2">
                            Assessment Infrastruktur
                            <HoverCard>
                                <HoverCardTrigger>
                                    <HelpCircle :size="20" class="text-white" />
                                </HoverCardTrigger>
                                <HoverCardContent>
                                    Pada Asessment Infrastruktur, Saudara diminta untuk melakukan assessment mandiri terkait
                                    dengan Sektor Infrastruktur, rencana infrastruktur prioritas, estimasi biaya dan opsi
                                    pembiayaan alternatif yang dipilih diakhiri dengan kesimpulan atas asessment
                                    infrastruktur, tantangan dan Rencana aksi sebagai hasil akhir tahap Asessment
                                    Infrastruktur ini.
                                </HoverCardContent>
                            </HoverCard>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="p-2 border-b border-black text-sm relative mb-4">
                    <div class="font-bold">Catatan :</div>
                    <div>1. Gunakan data resmi, seperti: realisasi APBD, Renstra, RPJMD, laporan kinerja, dan data
                        sektoral dari OPD terkait.</div>
                    <div>2. Usahakan deskripsi jelas, ringkas, dan berbasis angka/fakta (misalnya persentase layanan,
                        jumlah unit infrastruktur, atau standar pelayanan minimum).</div>
                </div>

                <!-- REALISASI ANGGARAN -->
                <table class="w-full border-collapse border-b border-black text-sm mb-4">
                    <tbody>
                        <tr>
                            <td
                                class="w-1/2 p-2  text-white text-center border-r border-b border-l border-black font-bold align-top bg-teal-500">
                                Tahun
                            </td>
                            <td
                                class="w-1/4 p-1 border-r border-b border-black font-bold text-center  text-white bg-teal-500">
                                {{ year2 }}
                            </td>
                            <td
                                class="w-1/4 p-1 border-r border-b border-black font-bold text-center  text-white bg-teal-500">
                                {{ year1 }}</td>
                        </tr>
                        <tr>
                            <td class="h-8 p-2 font-bold border-r border-b border-l border-black bg-[#ffceaa]"> %
                                Realisasi
                                Anggaran Infrastruktur berdasarkan UU HKPD</td>
                            <td
                                class="h-8 border-b border-r border-b border-black bg-[#ffceaa] text-center font-semibold p-0">
                                <input v-model="form.indicators[year2].infras_real" type="number" step="0.01"
                                    class="w-full h-full bg-transparent border-0 px-2 text-center outline-none focus:ring-0">
                            </td>
                            <td class="h-8 border-r border-b border-black bg-[#ffceaa] text-center font-semibold p-0">
                                <input v-model="form.indicators[year1].infras_real" type="number" step="0.01"
                                    class="w-full h-full bg-transparent border-0 px-2 text-center outline-none focus:ring-0">
                            </td>
                        </tr>
                        <tr>
                            <td class="h-8 p-2 font-bold border-r border-l border-black bg-[#ffceaa]">Rasio Kapasitas
                                Fiskal
                                Daerah</td>
                            <td class="h-8 border-b border-r border-black bg-[#ffceaa] text-center font-semibold p-0">
                                <input v-model="form.indicators[year2].fiscal_ratio" type="number" step="0.01"
                                    class="w-full h-full bg-transparent border-0 px-2 text-center outline-none focus:ring-0">
                            </td>
                            <td class="h-8 border-r border-b border-black bg-[#ffceaa] text-center font-semibold p-0">
                                <input v-model="form.indicators[year1].fiscal_ratio" type="number" step="0.01"
                                    class="w-full h-full bg-transparent border-0 px-2 text-center outline-none focus:ring-0">
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- SEKTOR INFRASTRUKTUR -->
                <table class="w-full border-collapse text-sm border-black mb-4">
                    <thead>
                        <tr>
                            <th class="w-2/5 border border-black bg-teal-500 text-white p-2 text-center align-middle">
                                Sektor
                                Infrastruktur</th>
                            <th
                                class="w-[10%] border border-black bg-teal-500  text-white p-1 text-center font-bold text-xs">
                                Baik<br>
                                <span class="font-normal text-[10px]">(Layanan sudah memadai...)</span>
                            </th>
                            <th
                                class="w-[10%] border border-black bg-teal-500  text-white p-1 text-center font-bold text-xs">
                                Cukup<br>
                                <span class="font-normal text-[10px]">(Sebagian besar cukup...)</span>
                            </th>
                            <th
                                class="w-[10%] border border-black bg-teal-500  text-white p-1 text-center font-bold text-xs">
                                Kurang<br>
                                <span class="font-normal text-[10px]">(Layanan masih kurang...)</span>
                            </th>
                            <th
                                class="w-auto border border-black bg-teal-500  text-white p-1 text-center font-bold text-xs">
                                Penjelasan Singkat<br>
                                <span class="font-normal text-[10px]">(Tambahkan uraian singkat...)</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="sector in sectors" :key="sector.key">
                            <td class="border border-black p-2 font-bold bg-[#ffceaa]">{{ sector.label }}</td>
                            <td class="border border-black bg-[#ffceaa] text-center p-0">
                                <label class="w-full h-full flex items-center justify-center cursor-pointer p-2">
                                    <input type="radio" v-model="form.services[sector.key].status" value="Baik"
                                        class="w-4 h-4 text-teal-600 focus:ring-teal-500">
                                </label>
                            </td>
                            <td class="border border-black bg-[#ffceaa] text-center p-0">
                                <label class="w-full h-full flex items-center justify-center cursor-pointer p-2">
                                    <input type="radio" v-model="form.services[sector.key].status" value="Cukup"
                                        class="w-4 h-4 text-teal-600 focus:ring-teal-500">
                                </label>
                            </td>
                            <td class="border border-black bg-[#ffceaa] text-center p-0">
                                <label class="w-full h-full flex items-center justify-center cursor-pointer p-2">
                                    <input type="radio" v-model="form.services[sector.key].status" value="Kurang"
                                        class="w-4 h-4 text-teal-600 focus:ring-teal-500">
                                </label>
                            </td>
                            <td class="border border-black bg-[#ffceaa] p-0">
                                <input v-model="form.services[sector.key].desc" type="text"
                                    class="w-full h-full min-h-[30px] bg-transparent border-0 px-2 py-1 outline-none focus:ring-0 text-sm">
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- RENCANA INFRASTRUKTUR PRIORITAS -->
                <div class="mb-1 text-[11px] text-gray-600 italic mt-6">*Berdasarkan perkiraan tingkat urgensi/dampak
                    positif terhadap pelayanan publik, ekonomi lokal, peningkatan PAD, dan lingkungan.</div>
                <table class="w-full border-collapse text-[12px] border-black mb-4">
                    <thead>
                        <tr>
                            <th class="border border-black bg-teal-500 text-white p-1 text-center">Rencana Infrastruktur
                                Prioritas
                            </th>
                            <th class="border border-black bg-teal-500 text-white p-1 text-center">Dampak yang
                                Diharapkan</th>
                            <th class="border border-black bg-teal-500 text-white p-1 text-center w-[8%]">Ranking
                                Prioritas*</th>
                            <th class="border border-black bg-teal-500 text-white p-1 text-center w-[12%]">Estimasi
                                Biaya (Rp)
                            </th>
                            <th class="border border-black bg-teal-500 text-white p-1 text-center w-[12%]">Dibiayai oleh
                                APBD/APBN
                                (Rp)</th>
                            <th class="border border-black bg-teal-500 text-white p-1 text-center w-[12%]">Kebutuhan
                                Pendanaan
                                Alternatif (Rp)</th>
                            <th class="border border-black bg-teal-500 text-white p-1 text-center w-[15%]">Opsi
                                Pembiayaan
                                Alternatif yang Dipilih</th>
                            <th class="border border-black bg-teal-500 text-white p-1 text-center w-[5%] bg-opacity-70">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(prio, index) in form.priorities" :key="'prio-' + index">
                            <td class="border border-black bg-[#ffceaa] p-0">
                                <textarea rows="2" v-model="prio.plan"
                                    class="w-full h-full bg-transparent border-0 px-1 py-1 outline-none focus:ring-0 text-xs resize-none"></textarea>
                            </td>
                            <td class="border border-black bg-[#ffceaa] p-0">
                                <textarea rows="2" v-model="prio.exp_outcome"
                                    class="w-full h-full bg-transparent border-0 px-1 py-1 outline-none focus:ring-0 text-xs resize-none"></textarea>
                            </td>
                            <td class="border border-black bg-[#ffceaa] p-0">
                                <input v-model="prio.rank" type="number"
                                    class="w-full h-full bg-transparent border-0 px-1 py-1 text-center outline-none focus:ring-0 text-xs">
                            </td>
                            <td class="border border-black bg-[#ffceaa] p-0">
                                <input v-model="prio.estimated_cost" type="number" step="0.01"
                                    class="w-full h-full bg-transparent border-0 px-1 py-1 outline-none focus:ring-0 text-xs text-right">
                            </td>
                            <td class="border border-black bg-[#ffceaa] p-0">
                                <input v-model="prio.fund_source" type="text"
                                    class="w-full h-full bg-transparent border-0 px-1 py-1 outline-none focus:ring-0 text-xs">
                            </td>
                            <td class="border border-black bg-[#ffceaa] p-0">
                                <input v-model="prio.alt_fund_need" type="number" step="0.01"
                                    class="w-full h-full bg-transparent border-0 px-1 py-1 outline-none focus:ring-0 text-xs text-right">
                            </td>
                            <td class="border border-black bg-[#ffceaa] p-0">
                                <input v-model="prio.alt_fund_source" type="text"
                                    class="w-full h-full bg-transparent border-0 px-1 py-1 outline-none focus:ring-0 text-xs">
                            </td>
                            <td class="border border-black bg-[#ffceaa] p-1 text-center">
                                <Button type="button" variant="ghost" size="sm"
                                    class="h-6 w-6 p-0 text-red-600 hover:text-red-800" @click="removePriority(index)">
                                    <Trash :size="14" />
                                </Button>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="8" class="p-1">
                                <Button type="button" variant="outline" size="sm" @click="addPriority"
                                    class="w-full flex gap-2 border-dashed border-gray-400">
                                    <Plus :size="16" /> Tambah Prioritas
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- KESIMPULAN -->
                <div class="mt-8 border border-black bg-[#ffceaa] text-sm relative mb-4">
                    <div
                        class="w-full bg-teal-500 text-white font-bold px-2 py-1 absolute top-0 left-0 right-0 border-b border-black">
                        Kesimpulan:<br>
                        Sektor infrastruktur apa saja yang sudah baik?
                    </div>
                    <textarea v-model="form.conclusion.advantage"
                        class="w-full h-24 bg-transparent border-0 p-2 pt-[52px] outline-none focus:ring-0 text-sm resize-none"></textarea>
                </div>

                <div class="border border-black bg-[#ffceaa] text-sm relative mb-4">
                    <div
                        class="w-full bg-teal-500 text-white font-bold px-2 py-1 absolute top-0 left-0 right-0 border-b border-black">
                        Sektor infrastruktur apa saja yang masih membutuhkan perbaikan? Apa yang menjadi tantangan?
                    </div>
                    <textarea v-model="form.conclusion.challenge"
                        class="w-full h-24 bg-transparent border-0 p-2 pt-[36px] outline-none focus:ring-0 text-sm resize-none"></textarea>
                </div>
                </div>

                    <div class="px-6 py-4 flex justify-between items-center bg-gray-50 border-t border-gray-200">
                        <Link href="/assessments">
                            <Button type="button" variant="outline" class="flex items-center gap-2">
                                <ChevronLeft :size="16" /> Back
                            </Button>
                        </Link>
                        <div class="flex items-center gap-4" v-if="can('create-assessments')">
                            <span v-show="form.recentlySuccessful" class="text-sm text-green-600 transition-opacity">Saved.</span>
                            <Button type="submit" :disabled="form.processing"
                                class="flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white transition-all shadow-sm">
                                <Save :size="16" /> Save Changes
                            </Button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
