<template>
  <div class="space-y-4">
    <div class="border-2 border-indigo-200 rounded-lg p-4 text-sm space-y-2">
      <div class="flex justify-between">
        <span class="text-gray-600">Price:</span>
        <span class="font-semibold text-gray-800">{{ formatPrice(basePrice) }}</span>
      </div>
      <div v-if="voucherDiscount > 0" class="flex justify-between text-green-600">
        <span class="text-gray-600">Voucher Discount:</span>
        <span class="font-semibold">- {{ formatPrice(voucherDiscount) }}</span>
      </div>
      <div v-if="walletDeduction > 0" class="flex justify-between text-green-600">
        <span class="text-gray-600">From Wallet:</span>
        <span class="font-semibold">- {{ formatPrice(walletDeduction) }}</span>
      </div>
      <div class="border-t border-gray-200 my-2"></div>
      <div class="flex justify-between text-lg">
        <span class="font-bold text-gray-800">Total Amount:</span>
        <span class="font-bold text-blue-800">{{ formatPrice(finalAmount) }}</span>
      </div>
    </div>

    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-2">Voucher Code (Optional)</label>
      <div class="flex gap-2">
        <input
          type="text"
          :value="voucherCode"
          @input="$emit('update:voucherCode', $event.target.value)"
          placeholder="Enter your code"
          class="flex-1 px-3 py-2 border-2 border-gray-300 rounded-md focus:outline-none focus:border-indigo-400"
          :disabled="isApplyingVoucher || voucherDiscount > 0"
        />
        <ResponsiveButton
          @click="$emit('apply-voucher')"
          type="dash-blue"
          class="px-4 py-2"
          :disabled="!voucherCode || isApplyingVoucher || voucherDiscount > 0"
        >
          <span v-if="isApplyingVoucher">...</span>
          <span v-else>Apply</span>
        </ResponsiveButton>
      </div>
    </div>

    <div v-if="walletBalance > 0" class="flex items-center">
      <input
        type="checkbox"
        id="use-wallet"
        :checked="useWalletBalance"
        @change="$emit('update:useWalletBalance', $event.target.checked)"
        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
      />
      <label for="use-wallet" class="ml-2 block text-sm text-gray-900">
        Use wallet balance (Available: <span class="font-bold">{{ formatPrice(walletBalance) }}</span>)
      </label>
    </div>

    <div class="flex gap-4">
      <ResponsiveButton @click="$emit('reset')" type="dash-gray" class="flex-1 py-3">
        Change Content
      </ResponsiveButton>
      <ResponsiveButton
        @click="$emit('pay')"
        type="light-green"
        :disabled="isSubmitting"
        class="flex-1 py-3"
      >
        <span v-if="isSubmitting">Processing...</span>
        <span v-else>Pay {{ formatPrice(finalAmount) }}</span>
      </ResponsiveButton>
    </div>
  </div>
</template>

<script setup>
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'

const props = defineProps({
  basePrice: Number,
  voucherCode: String,
  voucherDiscount: Number,
  walletDeduction: Number,
  finalAmount: Number,
  walletBalance: Number,
  useWalletBalance: Boolean,
  isApplyingVoucher: Boolean,
  isSubmitting: Boolean
})

defineEmits(['update:voucherCode', 'update:useWalletBalance', 'apply-voucher', 'reset', 'pay'])

const formatPrice = (amount) => {
  const currency = 'Rial'
  if (typeof amount !== 'number') return `0 ${currency}`
  return `${Math.round(amount).toLocaleString('en-US')} ${currency}`
}
</script>
