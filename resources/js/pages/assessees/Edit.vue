<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ChevronLeft } from '@lucide/vue';
import Label from '@/components/ui/label/Label.vue';
import Input from '@/components/ui/input/Input.vue';
import Combobox from '@/components/ui/combobox/Combobox.vue';
import { computed } from 'vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Edit Assessee',
                href: '/assessees/edit',
            },
        ],
    },
});

const props = defineProps<{
    assessee: {
        id: number,
        gov_id: number,
        user_id: number,
        position: string,
        contact: string,
        address: string
    },
    govs: Array<{ id: number, name: string, level: string }>,
    users: Array<{ id: number, name: string }>
}>();

const govOptions = computed(() => {
    return props.govs.map(gov => ({
        label: `${gov.name} (${gov.level})`,
        value: gov.id
    }))
})

const userOptions = computed(() => {
    return props.users.map(user => ({
        label: user.name,
        value: user.id
    }))
})

const form = useForm({
    gov_id: props.assessee.gov_id,
    user_id: props.assessee.user_id,
    position: props.assessee.position,
    contact: props.assessee.contact,
    address: props.assessee.address,
});

const submit = () => {
    form.put(`/assessees/${props.assessee.id}`);
}
</script>

<template>
    <Head title="Edit Assessee" />

    <Link href="/assessees" class="m-2 w-20 px-3 py-2 bg-blue-500 text-xs text-white rounded-full inline-block">
        <div class="flex items-center gap-2">
            <ChevronLeft :size="16" /> Back
        </div>
    </Link>

    <div class="p-4">
        <form @submit.prevent="submit">
            <div class="space-y-4 mt-4">
                <Label for="gov_id">Government</Label>
                <Combobox 
                    v-model="form.gov_id"
                    :options="govOptions"
                    placeholder="Select a government..."
                    search-placeholder="Search by name or level..."
                />
                <div class="text-small text-red-500" v-if="form.errors.gov_id">{{ form.errors.gov_id }}</div>
            </div>
            <div class="space-y-4 mt-4">
                <Label for="user_id">Name</Label>
                <Combobox 
                    v-model="form.user_id"
                    :options="userOptions"
                    placeholder="Select a user..."
                    search-placeholder="Search by name..."
                />
                <div class="text-small text-red-500" v-if="form.errors.user_id">{{ form.errors.user_id }}</div>
            </div>
            <div class="space-y-4 mt-4">
                <Label for="position">Position</Label>
                <Input v-model="form.position" type="text" placeholder="Position" />
                <div class="text-small text-red-500" v-if="form.errors.position">{{ form.errors.position }}</div>
            </div>
            <div class="space-y-4 mt-4">
                <Label for="contact">Contact</Label>
                <Input v-model="form.contact" type="text" placeholder="Contact" />
                <div class="text-small text-red-500" v-if="form.errors.contact">{{ form.errors.contact }}</div>
            </div>
            <div class="space-y-4 mt-4">
                <Label for="address">Address</Label>
                <Input v-model="form.address" type="text" placeholder="Address" />
                <div class="text-small text-red-500" v-if="form.errors.address">{{ form.errors.address }}</div>
            </div>
            <Button type="submit" :disabled="form.processing" class="mt-4">Update</Button>
        </form>
    </div>
</template>
