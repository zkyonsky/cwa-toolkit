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
                title: 'Assessee Create',
                href: '/assessees/create',
            },
        ],
    },
});
const props = defineProps<{
    govs: Array<{ id: number, name: string, level: string }>,
    user: { id: number, name: string }
}>();

const govOptions = computed(() => {
    return props.govs.map(gov => ({
        label: `${gov.name} (${gov.level})`,
        value: gov.id
    }))
})

const form = useForm({
    gov_id: '',
    position: '',
    contact: '',
    address: '',
});

const submit = () => {
    form.post('/assessees');
}

</script>

<template>
    <Head title="Assessee Create" />

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
                <Label for="name">Name</Label>
                <Input type="text" :model-value="user.name" disabled />
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
            <Button type="submit" :disabled="form.processing" class="mt-4">Create</Button>
        </form>
    </div>
</template>
