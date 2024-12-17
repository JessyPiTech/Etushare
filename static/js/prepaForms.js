/**
 * Generic form submission handler
 * @param {string} formId - ID of the form to handle
 * @param {Object} actionMap - Mapping of form fields to API action and parameters
 * @param {function} [preprocessData] - Optional function to preprocess form data
 */
function setupFormSubmission(formId, actionMap, preprocessData) {
    const form = document.getElementById(formId);
    const errorElement = document.getElementById(`${formId}Error`);
    form.addEventListener("submit", async (event) => {
        event.preventDefault();
    
    
        const formData = {};
        actionMap.fields.forEach(field => {
            const input = document.getElementById(field.id);
            formData[field.key] = input.value.trim();
        });
        
        if (preprocessData) {
            preprocessData(formData);
        }
    
        const requestData = {
            action: actionMap.action,
            ...formData
        };
        
        if (errorElement) {
            errorElement.textContent = "";
        }
        try {
            const result = await postData(apiUrl, requestData);
            if (result.error) {
                // Display error
                if (errorElement) {
                    errorElement.textContent = `Error: ${result.error}`;
                }
            } else {
                // Success handling
                if (result.success) {
                    if (result.redirect) {
                        // Redirect to specified page
                        window.location.href = result.redirect;
                    } else {
                        alert(`${actionMap.successMessage || 'Operation'} successful!`);
                    }
                    console.log(result);
                    form.reset();
                }
            }
        } catch (error) {
            console.error("Submission error:", error);
            if (errorElement) {
                errorElement.textContent = `Error: ${error.message}`;
            }
        }
    });
}
document.addEventListener('DOMContentLoaded', () => {
    Object.keys(formConfigurations).forEach(formId => {
        setupFormSubmission(
            formId, 
            formConfigurations[formId], 
            formConfigurations[formId].preprocessData
        );
    });
});