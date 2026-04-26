<script setup lang="ts">
import { ref, computed } from 'vue'
import {
  ComboboxRoot,
  ComboboxInput,
  ComboboxContent,
  ComboboxViewport,
  ComboboxItem,
  ComboboxItemIndicator,
  ComboboxPortal,
  ComboboxTrigger,
  ComboboxAnchor,
} from 'reka-ui'
import { Check, ChevronsUpDown, Search } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

interface Option {
  label: string
  value: string | number
}

const props = defineProps<{
  options: Option[]
  modelValue?: string | number
  placeholder?: string
  searchPlaceholder?: string
  emptyMessage?: string
  class?: string
}>()

const emits = defineEmits<{
  (e: 'update:modelValue', value: string | number): void
}>()

const searchTerm = ref('')
const open = ref(false)

const filteredOptions = computed(() => {
  if (!searchTerm.value) return props.options
  const search = searchTerm.value.toLowerCase()
  return props.options.filter((opt) => 
    opt.label.toLowerCase().includes(search)
  )
})

const selectedLabel = computed(() => {
  const selected = props.options.find(opt => opt.value === props.modelValue)
  return selected ? selected.label : null
})

function handleSelect(val: string | number) {
  emits('update:modelValue', val)
  open.value = false
  searchTerm.value = ''
}
</script>

<template>
  <ComboboxRoot 
    :model-value="modelValue" 
    v-model:open="open"
    @update:model-value="handleSelect"
  >
    <ComboboxAnchor as-child>
      <div class="relative w-full">
        <ComboboxTrigger
          :class="cn(
            'flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
            props.class
          )"
        >
          <span v-if="selectedLabel" class="truncate">{{ selectedLabel }}</span>
          <span v-else class="text-muted-foreground">{{ placeholder ?? 'Select option...' }}</span>
          <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
        </ComboboxTrigger>
      </div>
    </ComboboxAnchor>

    <ComboboxPortal>
      <ComboboxContent
        position="popper"
        :side-offset="5"
        class="z-50 min-w-[var(--reka-combobox-trigger-width)] overflow-hidden rounded-md border bg-popover text-popover-foreground shadow-md data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2"
      >
        <div class="flex items-center border-b px-3" icon-slot>
          <Search class="mr-2 h-4 w-4 shrink-0 opacity-50" />
          <ComboboxInput
            v-model="searchTerm"
            :placeholder="searchPlaceholder ?? 'Search...'"
            class="flex h-10 w-full rounded-md bg-transparent py-3 text-sm outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50"
          />
        </div>

        <ComboboxViewport class="p-1 max-h-[300px] overflow-y-auto">
          <div v-if="filteredOptions.length === 0" class="py-6 text-center text-sm">
            {{ emptyMessage ?? 'No options found.' }}
          </div>
          
          <ComboboxItem
            v-for="option in filteredOptions"
            :key="option.value"
            :value="option.value"
            class="relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50 cursor-pointer"
          >
            <span class="absolute left-2 flex h-3.5 w-3.5 items-center justify-center">
              <ComboboxItemIndicator>
                <Check class="h-4 w-4" />
              </ComboboxItemIndicator>
            </span>
            <span>{{ option.label }}</span>
          </ComboboxItem>
        </ComboboxViewport>
      </ComboboxContent>
    </ComboboxPortal>
  </ComboboxRoot>
</template>
