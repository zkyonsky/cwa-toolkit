<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Pencil, Trash, UserPlus, Rocket, Upload, Download, X } from '@lucide/vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
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
import { ref } from 'vue'

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Users',
        href: '/users',
      },
    ],
  },
});

defineProps({
  users: Array<{ id: number, name: string, email: string, roles?: Array<{ id: number, name: string }> }>
});

const page = usePage<any>();
const showImportModal = ref(false);
const csvFile = ref<File | null>(null);
const isUploading = ref(false);

const handleDelete = (id: number) => {
  if (confirm("Are you sure you want to delete this user?")) {
    router.delete(`/users/${id}`);
  }
}

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    csvFile.value = target.files[0];
  }
}

const handleImport = () => {
  if (!csvFile.value) return;

  isUploading.value = true;

  const formData = new FormData();
  formData.append('csv_file', csvFile.value);

  router.post('/users/import-csv', formData, {
    forceFormData: true,
    onFinish: () => {
      isUploading.value = false;
      showImportModal.value = false;
      csvFile.value = null;
    },
  });
}

</script>

<template>

  <Head title="Users" />

  <Alert class="m-2 bg-gray-100" v-if="page.props.flash?.message">
    <Rocket :size="16" />
    <AlertTitle>Notification</AlertTitle>
    <AlertDescription>
      {{ page.props.flash.message }}
    </AlertDescription>
  </Alert>

  <div class="flex items-center gap-2 m-2">
    <Link href="/users/create" class="px-3 py-2 bg-primary text-xs text-white rounded-full">
      <div class="flex items-center gap-2">
        <UserPlus :size="16" /> User
      </div>
    </Link>

    <button @click="showImportModal = true"
      class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-xs text-white rounded-full transition-colors">
      <div class="flex items-center gap-2">
        <Upload :size="16" /> Import CSV
      </div>
    </button>
  </div>

  <!-- Import CSV Modal -->
  <div v-if="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50" @click="showImportModal = false"></div>

    <!-- Modal Content -->
    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold">Import Users from CSV</h3>
        <button @click="showImportModal = false"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
          <X :size="20" />
        </button>
      </div>

      <div class="space-y-4">
        <!-- Download Template -->
        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md">
          <p class="text-sm text-blue-700 dark:text-blue-300 mb-2">
            Download the CSV template first to ensure proper formatting:
          </p>
          <a href="/users/download-template"
            class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded-md transition-colors">
            <Download :size="14" /> Download Template
          </a>
        </div>

        <!-- File Upload -->
        <div>
          <label class="block text-sm font-medium mb-1">Select CSV File</label>
          <input type="file" accept=".csv" @change="handleFileChange"
            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-primary/90 file:cursor-pointer" />
        </div>

        <!-- Info -->
        <div class="text-xs text-gray-500 dark:text-gray-400">
          <p><strong>CSV columns:</strong> name, email, password, role, assessee (yes/no), gov_id</p>
          <p class="mt-1">Max file size: 2MB</p>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-2 pt-2">
          <button @click="showImportModal = false"
            class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            Cancel
          </button>
          <button @click="handleImport" :disabled="!csvFile || isUploading"
            class="px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-md disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
            {{ isUploading ? 'Importing...' : 'Import' }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="mx-auto my-4 px-4 w-full lg:w-[80%]">


    <Table class="m-2">
      <TableCaption>A list of users.</TableCaption>
      <TableHeader>
        <TableRow>
          <TableHead class="w-[100px]">
            Name
          </TableHead>
          <TableHead>Email</TableHead>
          <TableHead>Roles</TableHead>
          <TableHead class="text-right">
            Actions
          </TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-for="user in users" :key="user.id">
          <TableCell class="font-medium">
            {{ user.name }}
          </TableCell>
          <TableCell>{{ user.email }}</TableCell>
          <TableCell>
            <div class="flex flex-wrap gap-1">
              <span v-for="role in user.roles" :key="role.id"
                class="px-2 py-1 bg-gray-200 text-gray-800 rounded-md text-xs">
                {{ role.name }}
              </span>
            </div>
          </TableCell>
          <TableCell class="text-right">
            <Link 
            v-if="can('edit-users')"
            :href="`/users/${user.id}/edit`">
              <Button class="mr-2 size-6">
                <Pencil />
              </Button>
            </Link>
            <Button 
            v-if="can('delete-users')"
            variant="destructive" class="size-6" @click="handleDelete(user.id)">
              <Trash />
            </Button>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>
