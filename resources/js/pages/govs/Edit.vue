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
                title: 'Edit Gov',
                href: '/govs/edit',
            },
        ],
    },
});

const props = defineProps<{
    gov: {
        id: number,
        code: string,
        name: string,
        level: string
    }
}>();

const form = useForm({
    code: props.gov.code,
    name: props.gov.name,
    level: props.gov.level,
});

const submit = () => {
    form.put(`/govs/${props.gov.id}`);
}
</script>

<template>
    <Head title="Edit Gov" />

    <Link href="/govs" class="m-2 w-20 px-3 py-2 bg-blue-500 text-xs text-white rounded-full inline-block">
        <div class="flex items-center gap-2">
            <ChevronLeft :size="16" /> Back
        </div>
    </Link>

    <div class="p-4">
        <form @submit.prevent="submit">
            <div class="space-y-4 mt-4">
                <Label for="code">Code</Label>
                <Input v-model="form.code" type="text" placeholder="Code" />
                <div class="text-small text-red-500" v-if="form.errors.code">{{ form.errors.code }}</div>
            </div>
            <div class="space-y-4 mt-4">
                <Label for="name">Name</Label>
                <Input v-model="form.name" type="text" placeholder="Name" />
                <div class="text-small text-red-500" v-if="form.errors.name">{{ form.errors.name }}</div>
            </div>
            <div class="space-y-4 mt-4">
                <Label for="level">Level</Label>
                 <select v-model="form.level" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background disabled:cursor-not-allowed disabled:opacity-50">
                    <option disabled value="">Select level...</option>
                    <option value="Provinsi">Provinsi</option>
                    <option value="Kabupaten/Kota">Kabupaten/Kota</option>
                </select>
                <div class="text-small text-red-500" v-if="form.errors.level">{{ form.errors.level }}</div>
            </div>
            <Button type="submit" :disabled="form.processing" class="mt-4">Update</Button>
        </form>
    </div>
</template>
