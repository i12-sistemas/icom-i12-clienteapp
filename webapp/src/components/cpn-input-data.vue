<template>
<q-input v-model="dFormatada" ref="txtdata" :readonly="readonly" :label="label" @change="onChange" maxlength="10"
  :outlined="outlined" :dense="dense" :stack-label="stacklabel" :disable="disable"
  >
  <template v-slot:append v-if="!readonly">
    <q-btn icon='date_range' dense flat round>
      <q-popup-proxy ref='qDateProxy'>
        <q-date v-model='d' @input="$refs.qDateProxy.hide()" today-btn/>
      </q-popup-proxy>
    </q-btn>
    <q-btn icon='clear' dense flat round v-if="clearable" @click="actClear" />
  </template>
  <q-tooltip :delay="700">
    <div v-if="!d">Nenhuma data informada</div>
    <div v-if="d">{{ $helpers.datetimeRelativeToday(d) }}</div>
    <div v-if="d">{{ $helpers.datetimeFormat(d, 'LL') }}</div>
  </q-tooltip>
</q-input>
</template>

<script>
import moment from 'moment'
export default {
  props: [
    'label', 'value', 'outlined', 'dense', 'clearable', 'stacklabel', 'readonly', 'disable'
  ],
  data: () => ({
    d: null,
    dFormatada: null
  }),
  mounted () {
    if (this.value) this.d = this.value
  },
  watch: {
    value: function (val) {
      this.d = val
      this.dFormatada = this.$helpers.dateToBR(this.d)
    },
    d: function (val) {
      // this.value = val
      this.dFormatada = this.$helpers.dateToBR(val)
      this.$emit('input', val)
    }
  },
  methods: {
    actClear () {
      this.dFormatada = null
      this.$emit('clear')
    },
    onChange (e) {
      moment.locale('pt-br')
      var str = e.target.value
      var dh = moment(str, 'DD/MM/YYYY')
      if (dh.isValid()) {
        this.d = dh.format('YYYY/MM/DD')
      } else {
        this.d = ''
      }
    },
    verplaatsFocus () {
      document.activeElement.blur()
      this.$refs.qDateProxy.show()
    },
    formatteerDatum (x) {
      return this.$helpers.dateToBR(x)
    }
  }
}
</script>
