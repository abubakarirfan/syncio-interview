import axios from 'axios';

const API_URL = 'http://localhost:8000/api';

export const sendPayload = async (payload) => {
    try {
        const response = await axios.post(`${API_URL}/payloads/receive`, payload);
        return response.data;
    } catch (error) {
        throw error.response.data;
    }
};

export const comparePayloads = async () => {
    try {
        const response = await axios.get(`${API_URL}/payloads/compare`);
        return response.data;
    } catch (error) {
        throw error.response.data;
    }
};

export const resetPayloads = async () => {
    return await axios.delete(`${API_URL}/payloads/reset`);
};

