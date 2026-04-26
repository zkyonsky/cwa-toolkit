<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ChevronLeft, Save, Plus, Trash, HelpCircle } from '@lucide/vue';
import { ref, computed } from 'vue';
import AssessmentStepper from '@/components/AssessmentStepper.vue';
import { can } from '@/lib/can';
import {
    HoverCard,
    HoverCardContent,
    HoverCardTrigger,
} from '@/components/ui/hover-card'
import { HelpCircleIcon } from 'lucide-vue-next';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Edit Assessment',
                href: '/assessments/edit',
            },
        ],
    },
});

const props = defineProps<{
    assessment: {
        id: number,
        assessee_id: number,
        date: string,
        info: string | null,
        result: string | null
    },
    assessee: {
        id: number,
        user: { name: string },
        position: string,
        contact: string,
        address: string,
        gov: { name: string }
    }
}>();

let parsedInfo = {
    pengalaman_jabatan: Array.from({ length: 4 }, () => ({ jabatan: '', tahun: '', ket: '' })),
    instansi_tlp: '',
    data_tambahan: [{ key: '', value: '' }]
};

if (props.assessment.info) {
    try {
        const infoObj = JSON.parse(props.assessment.info);
        if (infoObj.pengalaman_jabatan && infoObj.pengalaman_jabatan.length > 0) {
            parsedInfo.pengalaman_jabatan = infoObj.pengalaman_jabatan;
        }
        if (infoObj.instansi_tlp !== undefined) {
            parsedInfo.instansi_tlp = infoObj.instansi_tlp;
        }
        if (infoObj.data_tambahan && infoObj.data_tambahan.length > 0) {
            parsedInfo.data_tambahan = infoObj.data_tambahan;
        }
    } catch (e) {
        console.error("Failed to parse info", e);
    }
}

const infoData = ref(parsedInfo);

const form = useForm({
    assessee_id: props.assessment.assessee_id,
    date: props.assessment.date || '',
    info: '',
    result: props.assessment.result || '',
    position: props.assessee.position || '',
    contact: props.assessee.contact || '',
});


const submit = () => {
    form.info = JSON.stringify(infoData.value);
    form.put(`/assessments/${props.assessment.id}`);
}

const addDataTambahan = () => {
    infoData.value.data_tambahan.push({ key: '', value: '' });
};

const removeDataTambahan = (index: number) => {
    if (infoData.value.data_tambahan.length > 1) {
        infoData.value.data_tambahan.splice(index, 1);
    } else {
        infoData.value.data_tambahan[0] = { key: '', value: '' };
    }
};

const page = usePage<any>();

const isUser = computed(() => {
    const roles = page.props.auth.roles || [];
    return roles.includes('User');
})

</script>

<template>

    <Head title="Edit Assessment" />

    

    <AssessmentStepper :assessment-id="assessment.id" :current-step="1" />

    <div class="p-6 bg-gray-50 min-h-screen font-sans">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200">
                <div class="bg-teal-600 text-white text-center font-bold text-xl py-4 shadow-inner">
                    <div class="flex items-center justify-center gap-2">
                        Data Pemda
                        <HoverCard>
                            <HoverCardTrigger>
                                <HelpCircle :size="20" class="text-white" />
                            </HoverCardTrigger>
                            <HoverCardContent>
                                Pada Data Pemda, Saudara diminta untuk mengisikan data Pemerintah Daerah (Pemda) beserta data
                                peserta yang diperlukan dalam korespondensi pada kegiatan pelatihan ini.
                            </HoverCardContent>
                        </HoverCard>
                    </div>
                </div>

                <form @submit.prevent="submit" class="p-6">
                    <table class="w-full border-collapse text-sm border-gray-300 border">
                        <tbody>
                            <!-- Nama Pemda -->
                        <tr>
                            <td class="w-1/4 border border-gray-300 bg-teal-500 text-white font-semibold px-2 py-1">Nama
                                Pemda</td>
                            <td colspan="3" class="border border-gray-300 bg-gray-50 px-2 py-1">
                                {{ props.assessee.gov.name }}
                            </td>
                        </tr>

                        <!-- Nama -->
                        <tr>
                            <td class="w-1/4 border border-gray-300 bg-teal-500 text-white font-semibold px-2 py-1">Nama
                                Asesi</td>
                            <td colspan="3" class="border border-gray-300 bg-gray-50 px-2 py-1">
                                {{ props.assessee.user.name }}
                            </td>
                        </tr>

                        <!-- Jabatan -->
                        <tr>
                            <td class="w-1/4 border border-gray-300 bg-teal-500 text-white font-semibold px-2 py-1">
                                Jabatan</td>
                            <td colspan="3" class="border border-gray-300 bg-gray-50 p-0">
                                <input v-model="form.position" type="text"
                                    class="w-full bg-transparent border-0 px-2 py-1 outline-none focus:ring-0 text-sm">
                            </td>
                        </tr>

                        <!-- No kontak -->
                        <tr>
                            <td class="w-1/4 border border-gray-300 bg-teal-500 text-white font-semibold px-2 py-1">No
                                kontak</td>
                            <td colspan="3" class="border border-gray-300 bg-gray-50 p-0">
                                <input v-model="form.contact" type="text"
                                    class="w-full bg-transparent border-0 px-2 py-1 outline-none focus:ring-0 text-sm">
                            </td>
                        </tr>

                        <!-- Pengalaman Jabatan -->
                        <tr>
                            <td :rowspan="infoData.pengalaman_jabatan.length + 1"
                                class="w-1/4 border border-gray-300 bg-teal-500 text-white font-semibold px-2 py-1 align-top">
                                Pengalaman Jabatan
                            </td>
                            <td class="w-1/2 border border-gray-300 bg-teal-100/50 font-bold px-2 py-1 text-center">
                                Jabatan
                            </td>
                            <td class="w-[12.5%] border border-gray-300 bg-teal-100/50 font-bold px-2 py-1 text-center">
                                Tahun
                            </td>
                            <td class="w-[12.5%] border border-gray-300 bg-teal-100/50 font-bold px-2 py-1 text-center">
                                ket
                            </td>
                        </tr>
                        <tr v-for="(item, index) in infoData.pengalaman_jabatan" :key="'pj-' + index">
                            <td class="border border-gray-300 bg-gray-50 p-0">
                                <input v-model="item.jabatan" type="text"
                                    class="w-full bg-transparent border-0 px-2 py-1 outline-none focus:ring-0 text-sm">
                            </td>
                            <td class="border border-gray-300 bg-gray-50 p-0">
                                <input v-model="item.tahun" type="text"
                                    class="w-full bg-transparent border-0 px-2 py-1 outline-none focus:ring-0 text-sm text-center">
                            </td>
                            <td class="border border-gray-300 bg-gray-50 p-0">
                                <input v-model="item.ket" type="text"
                                    class="w-full bg-transparent border-0 px-2 py-1 outline-none focus:ring-0 text-sm">
                            </td>
                        </tr>

                        <!-- Instansi saat ini -->
                        <tr>
                            <td rowspan="2"
                                class="border border-gray-300 bg-teal-500 text-white font-semibold px-2 py-1 align-top">
                                Instansi saat ini</td>
                            <td class="border border-gray-300 bg-teal-100/50 font-bold px-2 py-1">Alamat:</td>
                            <td colspan="2" class="border border-gray-300 bg-gray-50 px-2 py-1 leading-snug">
                                {{ props.assessee.address }}
                            </td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 bg-teal-100/50 font-bold px-2 py-1">Tlp:</td>
                            <td colspan="2" class="border border-gray-300 bg-gray-50 p-0">
                                <input v-model="infoData.instansi_tlp" type="text"
                                    class="w-full bg-transparent border-0 px-2 py-1 outline-none focus:ring-0 text-sm">
                            </td>
                        </tr>

                        <!-- Data lain -->
                        <tr>
                            <td class="border border-gray-300 bg-teal-500 text-white font-semibold px-2 py-1">Data lain
                            </td>
                            <td class="border border-gray-300 bg-teal-100/50 font-bold px-2 py-1">Tanggal Penilaian
                                Mandiri
                            </td>
                            <td colspan="2" class="border border-gray-300 bg-gray-50 p-0">
                                <input v-model="form.date" type="text"
                                    class="w-full bg-transparent border-0 px-2 py-1 outline-none focus:ring-0 text-sm">
                            </td>
                        </tr>

                        <!-- Data Tambahan -->
                        <tr>
                            <td colspan="3"
                                class="border border-gray-300 bg-teal-600 text-white font-bold px-2 py-1 text-center">
                                Data Tambahan</td>
                            <td class="border border-gray-300 bg-teal-600 p-1 text-center">
                                <Button type="button" variant="ghost" size="sm" @click="addDataTambahan"
                                    class="h-6 w-full p-0 text-white hover:bg-teal-700">
                                    <Plus :size="16" />
                                </Button>
                            </td>
                        </tr>
                        <tr v-for="(item, index) in infoData.data_tambahan" :key="'dt-' + index">
                            <td class="border border-gray-300 bg-teal-500 text-white font-bold px-2 py-1">
                                <input v-model="item.key" type="text" placeholder="Keterangan..."
                                    class="w-full bg-transparent border-0 px-2 py-1 outline-none focus:ring-0 text-sm font-semibold placeholder:text-teal-200">
                            </td>
                            <td colspan="2" class="border border-gray-300 bg-gray-50 p-0">
                                <input v-model="item.value" type="text" placeholder="Nilai..."
                                    class="w-full bg-transparent border-0 px-2 py-1 outline-none focus:ring-0 text-sm">
                            </td>
                            <td class="border border-gray-300 bg-gray-50 p-1 text-center">
                                <Button type="button" variant="ghost" size="sm" @click="removeDataTambahan(index)"
                                    class="h-6 w-6 p-0 text-red-600 hover:text-red-800">
                                    <Trash :size="14" />
                                </Button>
                            </td>
                        </tr>

                        <!-- Catatan -->
                        <tr>
                            <td colspan="4" class="border border-gray-300 bg-gray-100 p-2 align-top h-32 relative">
                                <div class="font-bold text-sm mb-1">Catatan:</div>
                                <textarea v-model="form.result"
                                    class="w-full h-24 bg-transparent border-0 p-0 outline-none focus:ring-0 text-sm resize-none"></textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="form.hasErrors" class="mt-4 p-4 bg-red-50 text-red-600 rounded-md border border-red-200">
                    <ul class="list-disc pl-5">
                        <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                    </ul>
                </div>
            </form>
                <div class="px-6 py-4 flex justify-between items-center bg-gray-50 border-t border-gray-200 mt-6">
                    <Link href="/assessments">
                        <Button type="button" variant="outline" class="flex items-center gap-2">
                            <ChevronLeft :size="16" /> Back
                        </Button>
                    </Link>
                    <div v-if="can('create-assessments')">
                        <Button @click="submit" :disabled="form.processing"
                            class="flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white transition-all shadow-sm">
                            <Save :size="16" /> Save Changes
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
