<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ChevronLeft } from '@lucide/vue';
import Label from '@/components/ui/label/Label.vue';
import Input from '@/components/ui/input/Input.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Assessment Create',
                href: '/assessments/create',
            },
        ],
    },
});

const props = defineProps<{
    assessee_id: number,
}>();

const form = useForm({
    assessee_id: props.assessee_id,
    date: new Date().toISOString().split('T')[0],
});

const submit = () => {
    form.post('/assessments');
}
</script>

<template>

    <Head title="Assessment Create" />

    <Link href="/assessments" class="m-2 w-20 px-3 py-2 bg-blue-500 text-xs text-white rounded-full inline-block">
        <div class="flex items-center gap-2">
            <ChevronLeft :size="16" /> Back
        </div>
    </Link>

    <div class="p-4">
        <form @submit.prevent="submit">
            <div class="space-y-4 mt-4">
                <Label for="date">Tanggal Penilaian</Label>
                <Input v-model="form.date" type="date" placeholder="Date" />
                <div class="text-small text-red-500" v-if="form.errors.date">{{ form.errors.date }}</div>
            </div>
            <Button type="submit" :disabled="form.processing" class="mt-4">Create</Button>
        </form>
    </div>
</template>
