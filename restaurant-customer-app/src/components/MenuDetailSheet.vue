<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import { useMenuStore } from '../stores/menu'
import { useCartStore } from '../stores/cart'
import { rupiah } from '../services/format'
import { errorMessage } from '../services/api'
import { toast } from '../composables/useToast'

const props = defineProps({ menuId: { type: Number, required: true } })
const emit = defineEmits(['close'])

const menu = useMenuStore()
const cart = useCartStore()

const detail = ref(null)
const loadError = ref('')
const selected = reactive({}) // { [groupId]: [valueId, ...] }
const qty = ref(1)
const note = ref('')

onMounted(async () => {
  document.body.classList.add('no-scroll')
  try {
    detail.value = await menu.fetchDetail(props.menuId)
  } catch (e) {
    loadError.value = errorMessage(e, 'Detail menu belum bisa dimuat.')
  }
})
onBeforeUnmount(() => document.body.classList.remove('no-scroll'))

// Stok bisa berubah saat sheet terbuka (event realtime), jadi dibaca dari store, bukan dari detail.
const available = computed(() => menu.isAvailable(props.menuId))
const groups = computed(() => detail.value?.option_groups ?? [])

function isOn(group, value) {
  return (selected[group.id] || []).includes(value.id)
}

function toggle(group, value) {
  const cur = selected[group.id] || []
  if (group.selection_type === 'single') {
    // Grup wajib: pilihan tidak bisa dikosongkan. Grup opsional: ketuk lagi untuk membatalkan.
    selected[group.id] = cur.includes(value.id) ? (group.is_required ? cur : []) : [value.id]
  } else {
    selected[group.id] = cur.includes(value.id) ? cur.filter((id) => id !== value.id) : [...cur, value.id]
  }
}

const missing = computed(() =>
  groups.value.filter((g) => g.is_required && !(selected[g.id] || []).length).map((g) => g.name),
)

const chosen = computed(() =>
  groups.value.flatMap((g) =>
    g.values
      .filter((v) => (selected[g.id] || []).includes(v.id))
      .map((v) => ({ groupId: g.id, groupName: g.name, valueId: v.id, label: v.label, extraPrice: Number(v.extra_price) })),
  ),
)

const unit = computed(() => Number(detail.value?.price || 0) + chosen.value.reduce((s, o) => s + o.extraPrice, 0))
const total = computed(() => unit.value * qty.value)
const canAdd = computed(() => available.value && !missing.value.length)

function addToCart() {
  if (!canAdd.value) return
  cart.add({
    menuId: detail.value.id,
    name: detail.value.name,
    basePrice: Number(detail.value.price),
    qty: qty.value,
    note: note.value,
    options: chosen.value,
  })
  toast(`${detail.value.name} masuk keranjang`, 'ok', 2200)
  emit('close')
}
</script>

<template>
  <div class="sheet-backdrop" @click.self="emit('close')">
    <section class="sheet" role="dialog" aria-modal="true" :aria-label="detail?.name || 'Detail menu'">
      <button class="sheet-close" type="button" aria-label="Tutup" @click="emit('close')">Tutup</button>

      <p v-if="loadError" class="note-error">{{ loadError }}</p>
      <p v-else-if="!detail" class="muted">Memuat detail menu...</p>

      <template v-else>
        <img v-if="detail.image_url" :src="detail.image_url" alt="" class="sheet-photo" />
        <h2 class="sheet-title">{{ detail.name }}</h2>
        <p class="sheet-price">{{ rupiah(detail.price) }}</p>
        <p v-if="detail.description" class="sheet-desc">{{ detail.description }}</p>

        <p v-if="!available" class="note-error">Menu ini sedang habis dan belum bisa dipesan.</p>

        <fieldset v-for="g in groups" :key="g.id" class="opt-group">
          <legend>
            {{ g.name }}
            <span class="opt-hint">{{ g.is_required ? 'Wajib pilih satu' : g.selection_type === 'multiple' ? 'Boleh pilih lebih dari satu' : 'Boleh dikosongkan' }}</span>
          </legend>
          <button
            v-for="v in g.values"
            :key="v.id"
            type="button"
            class="opt"
            :class="{ on: isOn(g, v), round: g.selection_type === 'single' }"
            :role="g.selection_type === 'single' ? 'radio' : 'checkbox'"
            :aria-checked="isOn(g, v)"
            @click="toggle(g, v)"
          >
            <span class="opt-mark" aria-hidden="true"></span>
            <span class="opt-label">{{ v.label }}</span>
            <span v-if="Number(v.extra_price) > 0" class="opt-extra">+{{ rupiah(v.extra_price) }}</span>
          </button>
        </fieldset>

        <label class="field">
          <span>Catatan untuk dapur (boleh dikosongkan)</span>
          <textarea v-model="note" rows="2" maxlength="300" placeholder="Contoh: tanpa bawang, saus dipisah"></textarea>
        </label>

        <div class="sheet-foot">
          <div class="stepper" aria-label="Jumlah">
            <button type="button" aria-label="Kurangi" :disabled="qty <= 1" @click="qty--">-</button>
            <output>{{ qty }}</output>
            <button type="button" aria-label="Tambah" :disabled="qty >= 99" @click="qty++">+</button>
          </div>
          <button type="button" class="btn btn-primary grow" :disabled="!canAdd" @click="addToCart">
            Masukkan keranjang, {{ rupiah(total) }}
          </button>
        </div>
        <p v-if="available && missing.length" class="muted small">Pilih dulu: {{ missing.join(', ') }}</p>
      </template>
    </section>
  </div>
</template>
