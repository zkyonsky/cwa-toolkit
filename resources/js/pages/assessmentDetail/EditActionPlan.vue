<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ChevronLeft, Save, Plus, Trash, GripVertical } from '@lucide/vue';
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

const form = useForm({
    actionPlans: props.actionPlans.length > 0
        ? props.actionPlans.map(p => ({ ...p }))
        : [{ conclusion: '', challenge: '', action_plan: '' }]
});

const uniqueChallenges = computed(() => {
    const seen = new Set();
    return props.challenges.filter(challenge => {
        const key = challenge.name;
        if (seen.has(key)) return false;
        seen.add(key);
        return true;
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

// Drag and Drop Logic
const draggedChallenge = ref<any>(null);

const onDragStart = (challenge: any) => {
    draggedChallenge.value = challenge;
};

const onDrop = (index: number) => {
    if (draggedChallenge.value) {
        form.actionPlans[index].challenge = draggedChallenge.value.name;

        // Auto-populate action plan from ALL associated actions
        if (draggedChallenge.value.challenge_actions && draggedChallenge.value.challenge_actions.length > 0) {
            const listItems = draggedChallenge.value.challenge_actions
                .map((action: any) => `<li>${action.name}</li>`)
                .join('');
            form.actionPlans[index].action_plan = `<ol>${listItems}</ol>`;
        } else if (draggedChallenge.value.actions) {
            // Fallback to the actions text field if exists
            const formattedActions = draggedChallenge.value.actions.replace(/\n/g, '<br>');
            form.actionPlans[index].action_plan = `<p>${formattedActions}</p>`;
        }

        draggedChallenge.value = null;
    }
};

const onDragOver = (event: DragEvent) => {
    event.preventDefault();
};

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
                    <Save :size="16" /> Save Changes
                </Button>
            </div>
        </div>
    </div>

    <AssessmentStepper :assessment-id="assessment.id" :current-step="9" />

    <div class="p-6 bg-gray-50 min-h-screen">
        <div class="max-w-[1200px] mx-auto">

            <div
                class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200 backdrop-blur-sm bg-white/90">
                <div class="bg-teal-600 text-white text-center font-bold text-2xl py-4 shadow-inner">
                    <div class="flex items-center justify-center gap-2">
                        5_Kesimpulan, Tantangan, dan Rencana Aksi
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
                                <th class="w-1/3 p-3 border-r border-gray-300 text-left font-semibold text-gray-700">
                                    Kesimpulan</th>
                                <th class="w-1/3 p-3 border-r border-gray-300 text-left font-semibold text-gray-700">
                                    Tantangan</th>
                                <th class="w-1/3 p-3 text-left font-semibold text-gray-700">Rencana Aksi</th>
                                <th class="w-[50px] p-3 text-center font-semibold text-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-orange-200 border-b border-gray-300 h-8">
                                <td colspan="5"></td>
                            </tr>
                            <tr v-for="(plan, index) in form.actionPlans" :key="index"
                                class="border-b border-gray-200 hover:bg-teal-50 transition-colors group">
                                <td
                                    class="p-3 border-r border-gray-200 text-center align-top text-gray-500 font-medium">
                                    {{ index + 1 }}
                                </td>
                                <td class="p-2 border-r border-gray-200 align-top">
                                    <textarea v-model="plan.conclusion" @input="autoResizeTextareas"
                                        class="w-full min-h-[80px] bg-transparent border-0 focus:ring-2 focus:ring-teal-500 rounded-lg p-2 text-sm resize-none outline-none transition-all overflow-hidden"
                                        placeholder="Tulis kesimpulan..."></textarea>
                                </td>
                                <td class="p-2 border-r border-gray-200 align-top relative" @drop="onDrop(index)"
                                    @dragover="onDragOver($event)">
                                    <div
                                        class="absolute inset-0 border-2 border-dashed border-teal-300 opacity-0 group-hover:opacity-100 rounded-lg m-1 pointer-events-none transition-opacity">
                                    </div>
                                    <textarea v-model="plan.challenge" @input="autoResizeTextareas"
                                        class="w-full min-h-[80px] bg-transparent border-0 focus:ring-2 focus:ring-teal-500 rounded-lg p-2 text-sm resize-none outline-none transition-all overflow-hidden"
                                        placeholder="Tarik tantangan ke sini..."></textarea>
                                    <div v-if="index === 0" class="mt-2 text-[10px] text-gray-400 italic">
                                        Hint: Daftar Tantangan dapat di-drag n drop ke sini.
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

            <!-- Hint Section: Daftar Tantangan -->
            <div class="mt-8 bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center gap-2 border-b pb-2">
                    <div class="w-2 h-6 bg-teal-500 rounded-full"></div>
                    Hint: Daftar Tantangan
                </h3>
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-[400px] overflow-y-auto p-2 scrollbar-thin scrollbar-thumb-teal-200">
                    <div v-for="challenge in uniqueChallenges" :key="challenge.id" draggable="true"
                        @dragstart="onDragStart(challenge)"
                        class="p-3 bg-teal-50 border border-teal-100 rounded-lg cursor-move hover:bg-teal-100 hover:shadow-md transition-all flex items-start gap-3 active:scale-95 group">
                        <GripVertical class="text-teal-300 mt-1 group-hover:text-teal-500 transition-colors"
                            :size="16" />
                        <div>
                            <div class="text-xs font-bold text-teal-700 mb-1 uppercase tracking-wider opacity-70">{{
                                challenge.category?.name || 'Kategori' }}</div>
                            <div class="text-sm text-gray-800 font-medium leading-tight">{{ challenge.name }}</div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-400 italic flex items-center gap-1">
                    <span class="inline-block w-2 h-2 bg-teal-400 rounded-full"></span>
                    Tarik (drag) item dari daftar di atas ke kolom "Tantangan" pada tabel untuk mengisi data secara
                    otomatis.
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
:deep(.action-plan-editor .ql-toolbar) {
    border-top: none;
    border-left: none;
    border-right: none;
    border-bottom: 1px solid #e5e7eb;
    background-color: #f9fafb;
    border-radius: 0.375rem 0.375rem 0 0;
    padding: 4px 8px;
}
:deep(.action-plan-editor .ql-container) {
    border: none;
    min-height: 80px;
    font-size: 0.875rem;
    font-family: inherit;
}
:deep(.action-plan-editor .ql-editor) {
    min-height: 80px;
}
</style>
