const baseUrl = `${window.location.protocol}//${window.location.hostname}:${window.location.port}`;
const apiUrl = `${baseUrl}/important/etushare/api/api_gateway.php`;
/**
 * Helper function to send POST requests.
 * @param {string} url - The endpoint URL.
 * @param {Object} data - The data to send in the request body.
 * @returns {Promise<Object>} - The JSON response.
 */
async function postData(url, data) {
    try {
        const response = await fetch(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(data),
        });
        
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        
        const jsonResponse = await response.json();
        console.log('API Response:', jsonResponse);
        
        return jsonResponse;
    } catch (error) {
        console.error("Error:", error);
        return { error: error.message };
    }
}
