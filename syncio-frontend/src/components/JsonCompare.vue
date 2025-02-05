<template>
    <div class="container">
        <h2>JSON Payload Comparison</h2>

        <button @click="sendFirstPayload" :disabled="loading || payload1Sent">
            Send Payload 1
        </button>

        <button @click="comparePayloads" :disabled="loading || !payload2Sent">
            Compare Payloads
        </button>

        <button @click="resetCache">Reset Payloads</button>

        <p v-if="status">{{ status }}</p>

        <div v-if="timer > 0">
            Sending second payload in {{ timer }}s...
        </div>

        <div v-if="loading">Loading...</div>

        <div v-if="error" class="error">
            {{ error }}
        </div>

``        <div v-if="differences">
            <h3>Differences:</h3>
            <table v-if="flattenedDifferences.length > 0">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Old Value</th>
                        <th>New Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in flattenedDifferences" :key="index">
                        <td>{{ item.field }}</td>
                        <td>
                            <!-- if the value is an object, format it -->
                            <span v-if="isObject(item.old)">{{ formatObject(item.old) }}</span>
                            <span v-else>{{ item.old }}</span>
                        </td>
                        <td>
                            <span v-if="isObject(item.new)">{{ formatObject(item.new) }}</span>
                            <span v-else>{{ item.new }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-else>No differences found.</p>
        </div>
    </div>
</template>

<script>
import payload1 from '../data/payload1';
import payload2 from '../data/payload2';
import { sendPayload, comparePayloads, resetPayloads } from '../services/api';

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
    computed: {
        // compute a flattened array of difference rows for our table.
        flattenedDifferences() {
            if (!this.differences) return [];
            return this.flattenDifferences(this.differences);
        }
    },
    methods: {
        // recursive function that flattens nested differences
        flattenDifferences(diff, prefix = '') {
            let result = [];
            if (typeof diff === 'object' && diff !== null) {
                // Check if this is a leaf difference in the form { old: ..., new: ... }
                if (
                    Object.keys(diff).length === 2 &&
                    diff.hasOwnProperty('old') &&
                    diff.hasOwnProperty('new')
                ) {
                    const oldVal = diff.old;
                    const newVal = diff.new;
                    const oldIsObj = oldVal && typeof oldVal === 'object';
                    const newIsObj = newVal && typeof newVal === 'object';
                    // If either side is an object, attempt to flatten its keys.
                    if (oldIsObj || newIsObj) {
                        // Collect all keys from both objects.
                        let keys = [];
                        if (oldIsObj) keys = keys.concat(Object.keys(oldVal));
                        if (newIsObj) keys = keys.concat(Object.keys(newVal));
                        keys = Array.from(new Set(keys)); // unique keys
                        // If there are keys to process, flatten each one.
                        if (keys.length > 0) {
                            keys.forEach((key) => {
                                const subOld = oldIsObj ? oldVal[key] : undefined;
                                const subNew = newIsObj ? newVal[key] : undefined;
                                const newPrefix = prefix ? `${prefix}.${key}` : key;
                                result = result.concat(
                                    this.flattenDifferences({ old: subOld, new: subNew }, newPrefix)
                                );
                            });
                            return result;
                        }
                    }
                    // If neither side is an object (or there’s nothing to flatten), treat as a leaf.
                    result.push({ field: prefix, old: oldVal, new: newVal });
                } else if (Array.isArray(diff)) {
                    // If diff is an array, iterate with index notation.
                    diff.forEach((item, index) => {
                        const newPrefix = prefix ? `${prefix}[${index}]` : `[${index}]`;
                        result = result.concat(this.flattenDifferences(item, newPrefix));
                    });
                } else {
                    // Otherwise, iterate over the keys of the object.
                    for (const key in diff) {
                        const newPrefix = prefix ? `${prefix}.${key}` : key;
                        result = result.concat(this.flattenDifferences(diff[key], newPrefix));
                    }
                }
            } else {
                // For primitive values, push a simple leaf.
                result.push({ field: prefix, old: diff, new: undefined });
            }
            return result;
        },

        // Helper: Determines if a value is an object (and not null).
        isObject(val) {
            return val && typeof val === 'object';
        },

        // Helper: Format an object as a JSON string (you could adjust formatting as desired).
        formatObject(obj) {
            return JSON.stringify(obj, null, 2);
        },
        async sendFirstPayload() {
            this.loading = true;
            this.error = null;
            try {
                await sendPayload(payload1);
                this.payload1Sent = true;
                this.status = "Payload 1 sent!";
                this.startTimer();
            } catch (err) {
                this.error = err.error || "Error sending Payload 1.";
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
                await sendPayload(payload2);
                this.payload2Sent = true;
                this.status = "Payload 2 sent automatically!";
            } catch (err) {
                this.error = err.error || "Error sending Payload 2.";
            } finally {
                this.loading = false;
            }
        },

        async comparePayloads() {
            this.loading = true;
            this.error = null;
            try {
                const data = await comparePayloads();
                this.differences = data.differences || {};
            } catch (err) {
                this.error = err.error || "Error comparing payloads.";
            } finally {
                this.loading = false;
            }
        },

        async resetCache() {
            this.loading = true;
            this.error = null;
            try {
                await resetPayloads();
                this.payload1Sent = false;
                this.payload2Sent = false;
                this.differences = null;
                this.status = "Cache cleared! You can send new payloads.";
            } catch (err) {
                this.error = err.error || "Error clearing cache.";
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

/* Table Styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

th,
td {
    border: 1px solid black;
    padding: 8px;
    text-align: left;
}

th {
    background-color: #f4f4f4;
}
</style>
