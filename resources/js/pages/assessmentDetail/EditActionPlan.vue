<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ChevronLeft, Save, Plus, Trash } from '@lucide/vue';
import { ref, computed, watch, nextTick, onMounted } from 'vue';
import AssessmentStepper from '@/components/AssessmentStepper.vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import { can } from '@/lib/can';
import { HoverCard, HoverCardContent, HoverCardTrigger } from "@/components/ui/hover-card";
import { HelpCircle } from "@lucide/vue";

const props = defineProps<{
    assessment: { id: number, assessee: { gov: { name: string } } },
    actionPlans: any[],
    challenges: any[]
}>();

const defaultAspects = [
    'Kondisi Infrastruktur Existing',
    'Kondisi Kemampuan Meminjam (DSCR)',
    'Kondisi Keuangan/Fiskal',
    'Kondisi Ekonomi',
    'Kondisi Politik',
];

const getInitialActionPlans = () => {
    if (props.actionPlans && props.actionPlans.length > 0) {
        return props.actionPlans.map(p => ({ ...p }));
    }
    return defaultAspects.map(aspect => ({
        conclusion: aspect,
        challenge: '',
        action_plan: '',
    }));
};

const form = useForm({
    actionPlans: getInitialActionPlans(),
});

const page = usePage<any>();

const isAdmin = computed(() => {
    const roles = page.props.auth?.roles || [];
    return roles.includes('Super Admin') || roles.includes('Admin');
});

const uniqueChallenges = computed(() => {
    const seen = new Set();
    return (props.challenges || []).filter(challenge => {
        const key = challenge.name;
        if (seen.has(key)) return false;
        seen.add(key);
        return true;
    });
});

const searchQuery = ref('');

const filteredChallenges = computed(() => {
    if (!searchQuery.value.trim()) return uniqueChallenges.value;
    const q = searchQuery.value.toLowerCase();
    return uniqueChallenges.value.filter((c: any) => {
        const cat = (c.category?.name || '').toLowerCase();
        const name = (c.name || '').toLowerCase();
        return cat.includes(q) || name.includes(q);
    });
});

const addRow = () => {
    form.actionPlans.push({ conclusion: '', challenge: '', action_plan: '' });
};

const removeRow = (index: number) => {
    form.actionPlans.splice(index, 1);
};

const submit = () => {
    form.put(`/assessment-details/${props.assessment.id}/action-plan`);
};

const autoResizeTextareas = async () => {
    await nextTick();
    document.querySelectorAll('textarea').forEach((textarea) => {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    });
};

onMounted(() => {
    autoResizeTextareas();
});

watch(() => form.actionPlans, () => {
    autoResizeTextareas();
}, { deep: true });

</script>

<template>

    <Head title="Rencana Aksi" />

    <div class="px-6 py-4 flex justify-between items-center bg-white border-b sticky top-0 z-10">
        <Link href="/assessments"
            class="px-4 py-2 bg-slate-100 text-sm text-slate-700 rounded-md hover:bg-slate-200 transition">
            <div class="flex items-center gap-2">
                <ChevronLeft :size="16" /> Back
            </div>
        </Link>
        <div class="flex items-center gap-4">
            <span v-show="form.recentlySuccessful" class="text-sm text-green-600 transition-opacity">Saved.</span>
            <div v-if="can('create-assessments')">
                <Button @click="submit" :disabled="form.processing"
                    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white shadow-lg transition-all active:scale-95">
                    <Save :size="16" /> Simpan dan Selesaikan
                </Button>
            </div>
        </div>
    </div>

    <AssessmentStepper :assessment-id="assessment.id" :current-step="8" />

    <div class="p-6 bg-gray-50 min-h-screen">
        <div class="max-w-[1200px] mx-auto">

            <div
                class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200 backdrop-blur-sm bg-white/90">
                <div class="bg-teal-600 text-white text-center font-bold text-2xl py-4 shadow-inner">
                    <div class="flex items-center justify-center gap-2">
                        Tantangan dan Rencana Aksi
                        <HoverCard>
                            <HoverCardTrigger>
                                <HelpCircle :size="20" class="text-white" />
                            </HoverCardTrigger>
                            <HoverCardContent>
                                Pada Tantangan dan Rencana Aksi, Saudara diminta untuk menginventarisir seluruh
                                kesimpulan, tantangan dan rencana aksi dari setiap tahapan sebelumnya untuk dapat
                                didiskusikan bersama pengajar dan peserta lainnya dan saling memberikan pendapat.
                            </HoverCardContent>
                        </HoverCard>
                    </div>
                </div>

                <div class="p-0">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-100 border-b border-gray-300">
                                <th
                                    class="w-[50px] p-3 border-r border-gray-300 text-center font-semibold text-gray-700">
                                    No</th>
                                <th class="w-1/4 p-3 border-r border-gray-300 text-left font-semibold text-gray-700">
                                    Aspek</th>
                                <th class="w-[36%] p-3 border-r border-gray-300 text-left font-semibold text-gray-700">
                                    Tantangan</th>
                                <th class="w-[36%] p-3 text-left font-semibold text-gray-700">Rencana Aksi</th>
                                <th class="w-[50px] p-3 text-center font-semibold text-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-orange-200 border-b border-gray-300 h-8">
                                <td colspan="5"></td>
                            </tr>
                            <tr v-for="(plan, index) in form.actionPlans" :key="index"
                                class="border-b border-gray-200 hover:bg-teal-50/50 transition-colors group">
                                <td
                                    class="p-3 border-r border-gray-200 text-center align-top text-gray-500 font-medium">
                                    {{ index + 1 }}
                                </td>
                                <td class="p-2 border-r border-gray-200 align-top">
                                    <textarea v-model="plan.conclusion" @input="autoResizeTextareas"
                                        class="w-full min-h-[80px] bg-transparent border-0 focus:ring-2 focus:ring-teal-500 rounded-lg p-2 text-sm resize-none outline-none transition-all overflow-hidden font-medium text-gray-800"
                                        placeholder="Tulis aspek..."></textarea>
                                </td>
                                <td class="p-2 border-r border-gray-200 align-top challenge-editor">
                                    <div class="bg-white rounded-md border border-gray-200">
                                        <QuillEditor 
                                            v-model:content="plan.challenge" 
                                            contentType="html" 
                                            theme="snow"
                                            placeholder="Tulis tantangan..."
                                        />
                                    </div>
                                </td>
                                <td class="p-2 align-top action-plan-editor">
                                    <div class="bg-white rounded-md border border-gray-200">
                                        <QuillEditor 
                                            v-model:content="plan.action_plan" 
                                            contentType="html" 
                                            theme="snow"
                                            placeholder="Tulis rencana aksi..."
                                        />
                                    </div>
                                </td>
                                <td class="p-3 text-center align-top">
                                    <Button type="button" variant="ghost" size="icon"
                                        class="text-red-500 hover:text-red-700 hover:bg-red-50 transition-colors"
                                        @click="removeRow(index)" :disabled="form.actionPlans.length === 1">
                                        <Trash :size="16" />
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="p-4 bg-gray-50 border-t border-gray-200 flex justify-center">
                    <Button type="button" variant="outline" @click="addRow"
                        class="flex items-center gap-2 border-dashed border-teal-500 text-teal-600 hover:bg-teal-50 px-8 py-2 rounded-full transition-all active:scale-95 shadow-sm">
                        <Plus :size="18" /> Tambah Baris
                    </Button>
                </div>
            </div>

            <!-- Hint Section: Daftar Tantangan (Khusus Super Admin & Admin) -->
            <div v-if="isAdmin" class="mt-8 bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b pb-4 mb-4">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800 flex items-center gap-2">
                            <div class="w-2 h-6 bg-teal-500 rounded-full"></div>
                            Hint: Daftar Tantangan
                            <span class="text-xs font-normal bg-amber-100 text-amber-800 px-2.5 py-0.5 rounded-full border border-amber-300">
                                Khusus Super Admin & Admin
                            </span>
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">Daftar referensi tantangan dan rekomendasi rencana aksi untuk bahan diskusi.</p>
                    </div>
                    <div class="w-full md:w-72">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari kategori atau tantangan..."
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-teal-500"
                        />
                    </div>
                </div>

                <div class="max-h-[450px] overflow-y-auto border border-gray-200 rounded-lg scrollbar-thin scrollbar-thumb-teal-200">
                    <table class="w-full border-collapse text-xs">
                        <thead class="sticky top-0 bg-teal-600 text-white z-10 shadow-sm">
                            <tr>
                                <th class="w-[45px] p-2.5 text-center font-semibold border-r border-teal-500">No</th>
                                <th class="w-[200px] p-2.5 text-left font-semibold border-r border-teal-500">Kategori</th>
                                <th class="p-2.5 text-left font-semibold border-r border-teal-500">Tantangan</th>
                                <th class="w-[40%] p-2.5 text-left font-semibold">Rekomendasi Rencana Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="(challenge, cIndex) in filteredChallenges" :key="challenge.id" class="hover:bg-teal-50/50 transition-colors">
                                <td class="p-2.5 text-center text-gray-500 align-top border-r border-gray-200 font-medium">{{ cIndex + 1 }}</td>
                                <td class="p-2.5 align-top border-r border-gray-200">
                                    <span class="inline-block bg-teal-50 text-teal-700 border border-teal-200 px-2 py-0.5 rounded font-semibold text-[11px]">
                                        {{ challenge.category?.name || '-' }}
                                    </span>
                                </td>
                                <td class="p-2.5 align-top border-r border-gray-200 text-gray-800">
                                    <div class="font-semibold text-gray-900 mb-1">{{ challenge.name }}</div>
                                    <div v-if="challenge.definition" class="text-gray-500 text-[11px] leading-relaxed italic">
                                        {{ challenge.definition }}
                                    </div>
                                </td>
                                <td class="p-2.5 align-top text-gray-700">
                                    <ol v-if="challenge.challenge_actions && challenge.challenge_actions.length > 0" class="list-decimal list-inside space-y-1">
                                        <li v-for="action in challenge.challenge_actions" :key="action.id" class="leading-relaxed">
                                            {{ action.name }}
                                        </li>
                                    </ol>
                                    <div v-else-if="challenge.actions" class="whitespace-pre-line leading-relaxed">
                                        {{ challenge.actions }}
                                    </div>
                                    <span v-else class="text-gray-400 italic">-</span>
                                </td>
                            </tr>
                            <tr v-if="filteredChallenges.length === 0">
                                <td colspan="4" class="p-8 text-center text-gray-500 italic">
                                    Tidak ada tantangan yang cocok dengan kata kunci "{{ searchQuery }}".
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.scrollbar-thin::-webkit-scrollbar {
    width: 6px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #ccf2f4;
    border-radius: 10px;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: #a4ebf3;
}

/* Custom styling for Quill to match the design */
:deep(.challenge-editor .ql-toolbar),
:deep(.action-plan-editor .ql-toolbar) {
    border-top: none;
    border-left: none;
    border-right: none;
    border-bottom: 1px solid #e5e7eb;
    background-color: #f9fafb;
    border-radius: 0.375rem 0.375rem 0 0;
    padding: 4px 8px;
}
:deep(.challenge-editor .ql-container),
:deep(.action-plan-editor .ql-container) {
    border: none;
    min-height: 80px;
    font-size: 0.875rem;
    font-family: inherit;
}
:deep(.challenge-editor .ql-editor),
:deep(.action-plan-editor .ql-editor) {
    min-height: 80px;
}
</style>
