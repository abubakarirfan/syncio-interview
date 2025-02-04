<template>
  <div class="container">
    <h2>JSON Payload Comparison</h2>

    <!-- Send First Payload -->
    <button @click="sendFirstPayload" :disabled="loading || payload1Sent">
      Send Payload 1
    </button>
    <p v-if="status">{{ status }}</p>

    <!-- Countdown Timer Before Sending Second Payload -->
    <div v-if="timer > 0">
      Sending second payload in {{ timer }}s...
    </div>

    <!-- Second Payload Auto-Sent Message -->
    <p v-if="payload2Sent">Payload 2 sent automatically!</p>

    <!-- Compare Button -->
    <button @click="comparePayloads" :disabled="loading || !payload2Sent">
      Compare Payloads
    </button>

    <!-- Loading Indicator -->
    <div v-if="loading">Loading...</div>

    <!-- Error Display -->
    <div v-if="error" class="error">
      {{ error }}
    </div>

    <!-- Display Differences -->
    <div v-if="differences">
      <h3>Differences:</h3>
      <pre>{{ JSON.stringify(differences, null, 2) }}</pre>
    </div>
  </div>
</template>

<script>
import { sendPayload, comparePayloads } from '../services/api';

export default {
  data() {
    return {
      loading: false,
      error: null,
      status: null,
      payload1Sent: false,
      payload2Sent: false,
      differences: null,
      timer: 0,
    };
  },
  methods: {
    async sendFirstPayload() {
      this.loading = true;
      this.error = null;

      try {
        const payload1 = {
          "id": 432232523,
          "title": "Syncio TShirt",
          "description": "<p>Lorem ipsum...</p>",
          "images": [
            { "id": 26372, "position": 1, "url": "https://cu.syncio.co/images/random_image_1.png" }
          ],
          "variants": [
            { "id": 433232, "sku": "SKUII10", "barcode": "BAR_CODE_230", "image_id": 26372, "inventory_quantity": 12 }
          ]
        };

        await sendPayload(payload1);
        this.payload1Sent = true;
        this.status = "Payload 1 sent!";
        this.startTimer();
      } catch (err) {
          this.error = err.error;
      } finally {
        this.loading = false;
      }
    },

    startTimer() {
      this.timer = 30;
      let interval = setInterval(() => {
        this.timer--;
        if (this.timer === 0) {
          clearInterval(interval);
          this.sendSecondPayload();
        }
      }, 1000);
    },

    async sendSecondPayload() {
      this.loading = true;
      this.error = null;

      try {
        const payload2 = {
          "id": 432232523,
          "title": "Syncio Hoodie",
          "description": "<p>Lorem ipsum...</p>",
          "images": [
            { "id": 26372, "position": 1, "url": "https://cu.syncio.co/images/random_image_1.png" },
            { "id": 34546, "position": 5, "url": "https://cu.syncio.co/images/random_image_16.png" }
          ],
          "variants": [
            { "id": 433232, "sku": "SKUII10", "barcode": "BAR_CODE_230", "image_id": 34566, "inventory_quantity": 12 }
          ]
        };

        await sendPayload(payload2);
        this.payload2Sent = true;
        this.status = "Payload 2 sent automatically!";
      } catch (err) {
          this.error = err.error;
      } finally {
        this.loading = false;
      }
    },

    async comparePayloads() {
      this.loading = true;
      this.error = null;

      try {
        const data = await comparePayloads();
        if (data.differences && Object.keys(data.differences).length > 0) {
          this.differences = data.differences;
        } else {
          this.differences = { message: "No differences found" };
        }
      } catch (err) {
        this.error = "Error comparing payloads: " + err.message;
      } finally {
        this.loading = false;
      }
    }
  },
};
</script>

<style scoped>
.container {
  max-width: 600px;
  margin: auto;
  text-align: center;
}

button {
  margin: 10px;
  padding: 10px 15px;
  font-size: 16px;
}

.error {
  color: red;
  font-weight: bold;
}
</style>
