<script setup lang="ts">
import { ref } from 'vue';
import { watchDebounced } from '@vueuse/core';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Pencil, Trash, Plus, Rocket } from '@lucide/vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import Input from '@/components/ui/input/Input.vue';
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { can } from '@/lib/can'

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Govs',
        href: '/govs',
      },
    ],
  },
});

const props = defineProps<{
  govs: {
    data: Array<{ id: number, code: string, name: string, level: string }>;
    links: Array<{ url: string | null, label: string, active: boolean }>;
  },
  filters: {
    search?: string;
  }
}>();

const page = usePage<any>();

const search = ref(props.filters?.search || '');

watchDebounced(search, (value) => {
  router.get('/govs', { search: value }, {
    preserveState: true,
    replace: true,
    preserveScroll: true
  });
}, { debounce: 300 });
const handleDelete = (id: number) => {
  if (confirm("Are you sure you want to delete this gov?")) {
    router.delete(`/govs/${id}`);
  }
}
</script>

<template>
  <Head title="Govs" />

  <Alert class="m-2 bg-gray-100" v-if="page.props.flash?.message">
    <Rocket :size="16" />
    <AlertTitle>Notification</AlertTitle>
    <AlertDescription>
      {{ page.props.flash.message }}
    </AlertDescription>
  </Alert>

  <Link href="/govs/create" class="m-2 w-max px-3 py-2 bg-blue-500 text-xs text-white rounded-full inline-block">
    <div class="flex items-center gap-2">
      <Plus :size="16" /> Gov
    </div>
  </Link>

  <div class="mx-auto my-4 px-4 w-full lg:w-[80%]">
    <div class="mb-4">
      <Input v-model="search" type="text" placeholder="Search by name..." class="max-w-sm" />
    </div>

    <Table>
      <TableCaption>A list of govs.</TableCaption>
    <TableHeader>
      <TableRow>
        <TableHead>Code</TableHead>
        <TableHead>Name</TableHead>
        <TableHead>Level</TableHead>
        <TableHead class="text-right">Actions</TableHead>
      </TableRow>
    </TableHeader>
    <TableBody>
      <TableRow v-for="gov in govs.data" :key="gov.id">
        <TableCell>{{ gov.code }}</TableCell>
        <TableCell class="font-medium">{{ gov.name }}</TableCell>
        <TableCell>{{ gov.level }}</TableCell>
        <TableCell class="text-right">
          <Link 
          v-if="can('edit-govs')"
          :href="`/govs/${gov.id}/edit`">
            <Button class="mr-2 size-6">
              <Pencil />
            </Button>
          </Link>
          <Button 
          v-if="can('delete-govs')"
          variant="destructive" class="size-6" @click="handleDelete(gov.id)">
            <Trash />
          </Button>
        </TableCell>
      </TableRow>
    </TableBody>
    </Table>

    <div class="mt-4 flex flex-wrap gap-1 justify-center">
      <template v-for="(link, key) in govs.links" :key="key">
        <Link
          v-if="link.url"
          :href="link.url"
          v-html="link.label"
          class="px-3 py-1 border rounded text-sm"
          :class="{ 'bg-blue-500 text-white border-blue-500': link.active, 'bg-white hover:bg-gray-100 text-gray-700': !link.active }"
        />
        <span
          v-else
          v-html="link.label"
          class="px-3 py-1 border rounded text-sm text-gray-400 bg-gray-50"
        ></span>
      </template>
    </div>
  </div>
</template>
