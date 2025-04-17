<template>
  <!-- This is an invisible component that only handles real-time notifications -->
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { toast } from '@/Components/ui/toast/use-toast';

const props = defineProps({
  userId: {
    type: Number,
    required: true
  }
});

let echoChannel = null;

onMounted(() => {
  // Only set up the listener if authenticated
  if (props.userId) {
    // Listen for clip shared events on the private user channel
    echoChannel = window.Echo.private(`user.${props.userId}`)
      .listen('.clip.shared', (e) => {
        toast({
          title: 'New Clip Shared',
          description: `Coach ${e.sender.name} has shared a new clip with you.`,
          variant: 'default',
          action: {
            label: 'View',
            onClick: () => window.location.href = `/clips/${e.id}`
          }
        });
      });
  }
});

onUnmounted(() => {
  // Clean up the Echo subscription when component is unmounted
  if (echoChannel) {
    echoChannel.stopListening('.clip.shared');
  }
});
</script> 