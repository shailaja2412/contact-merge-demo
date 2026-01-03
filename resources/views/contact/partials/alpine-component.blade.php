<script>
function contactManager() {
    return {
        // State
        open: false,
        deleteOpen: false,
        deleteId: null,
        viewOpen: false,
        viewContact: {},
        viewAdditionalFiles: [],
        viewCustomFields: {},
        isEdit: false,
        editId: null,
        mergeOpen: false,
        mergeConfirmOpen: false,
        mergeSecondaryContact: {},
        mergeMasterContact: {},
        mergeMasterContactId: null,
        mergeContacts: [],
        filteredMergeContacts: [],
        mergeSearchQuery: '',
        first_name: '',
        last_name: '',
        emails: [''],
        phone_numbers: [''],
        documents: [''],
        gender: '',
        profile_picture: '',
        profilePictureUrl: '',
        additionalFiles: [],
        customFieldValues: {},
        filterOpen: false,
        isSubmitting: false,
        fieldErrors: {},

        // Methods
        formatDateForInput(dateString) {
            if (!dateString) return '';
            try {
                if (/^\d{2}\/\d{2}\/\d{4}$/.test(dateString)) {
                    return dateString;
                }
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return dateString;
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return `${day}/${month}/${year}`;
            } catch (e) {
                return dateString;
            }
        },

        openCreate() {
            this.isEdit = false;
            this.editId = null;
            this.first_name = '';
            this.last_name = '';
            this.emails = [''];
            this.phone_numbers = [''];
            this.documents = [''];
            this.gender = '';
            this.profile_picture = '';
            this.profilePictureUrl = '';
            this.additionalFiles = [];
            this.customFieldValues = {};
            this.fieldErrors = {};
            
            // Reset file inputs
            this.$nextTick(() => {
                const form = document.getElementById('contact-form');
                if (form) {
                    // Reset profile picture input
                    const profileInput = form.querySelector('input[name="profile_picture"]');
                    if (profileInput) {
                        profileInput.value = '';
                    }
                    // Reset additional files inputs
                    const fileInputs = form.querySelectorAll('input[name="additional_files[]"]');
                    fileInputs.forEach(input => {
                        input.value = '';
                    });
                }
            });
            
            this.open = true;
        },

        openEdit(contact) {
            this.isEdit = true;
            this.editId = contact.id;
            this.first_name = contact.first_name;
            this.last_name = contact.last_name;
            this.emails = contact.emails && contact.emails.length ? contact.emails : (contact.email ? [contact.email] : ['']);
            this.phone_numbers = contact.phone_numbers && contact.phone_numbers.length ? contact.phone_numbers : (contact.phone_number ? [contact.phone_number] : ['']);
            this.documents = [''];
            this.gender = contact.gender;
            this.profile_picture = contact.profile_picture || '';
            this.profilePictureUrl = contact.profile_picture ? `/storage/${contact.profile_picture}` : '';

            // Parse additional files
            if (contact.additional_files && Array.isArray(contact.additional_files)) {
                this.additionalFiles = contact.additional_files;
            } else if (typeof contact.additional_files === 'string' && contact.additional_files) {
                this.additionalFiles = JSON.parse(contact.additional_files);
            } else {
                this.additionalFiles = [];
            }

            // Fetch custom field values
            fetch(`/api/contacts/${contact.id}/custom-fields`)
                .then(response => response.json())
                .then(data => {
                    // Convert to simple key-value format for edit form
                    // Handle both old format (just value) and new format (object with value, field_name, field_type)
                    const simpleData = {};
                    Object.keys(data).forEach(key => {
                        if (typeof data[key] === 'object' && data[key].value !== undefined) {
                            simpleData[key] = data[key].value;
                        } else {
                            simpleData[key] = data[key];
                        }
                    });
                    this.customFieldValues = simpleData;
                })
                .catch(error => console.error('Error fetching custom fields:', error));

            this.fieldErrors = {};
            this.open = true;
        },

        openView(contact) {
            this.viewContact = {
                id: contact.id,
                first_name: contact.first_name,
                last_name: contact.last_name,
                emails: (contact.emails && contact.emails.length) ? contact.emails : (contact.email ? [contact.email] : []),
                phone_numbers: (contact.phone_numbers && contact.phone_numbers.length) ? contact.phone_numbers : (contact.phone_number ? [contact.phone_number] : []),
                gender: contact.gender,
                profile_picture: contact.profile_picture || ''
            };

            let parsedFiles = [];
            if (contact.additional_files && Array.isArray(contact.additional_files)) {
                parsedFiles = contact.additional_files;
            } else if (typeof contact.additional_files === 'string' && contact.additional_files) {
                try {
                    parsedFiles = JSON.parse(contact.additional_files);
                } catch (e) {
                    parsedFiles = [];
                }
            }
            this.viewContact.additional_files = parsedFiles;
            this.viewAdditionalFiles = parsedFiles;
            
            // Fetch custom field values for view
            this.viewCustomFields = {};
            fetch(`/api/contacts/${contact.id}/custom-fields`)
                .then(response => response.json())
                .then(data => {
                    // Data should already be in object format with field_name from controller
                    this.viewCustomFields = data;
                })
                .catch(error => console.error('Error fetching custom fields:', error));
            
            this.viewOpen = true;
        },

        openMerge(contactId) {
            // Fetch contact data
            fetch(`/contacts/${contactId}/merge`)
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.error || 'Error loading contact data');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        if (typeof window.showToast === 'function') {
                            window.showToast(data.error, 'error');
                        } else {
                            alert(data.error);
                        }
                        return;
                    }
                    this.mergeSecondaryContact = data;
                    this.mergeOpen = true;
                    this.loadContactsForMerge(contactId);
                })
                .catch(error => {
                    console.error('Error fetching contact:', error);
                    if (typeof window.showToast === 'function') {
                        window.showToast(error.message || 'Error loading contact data', 'error');
                    } else {
                        alert(error.message || 'Error loading contact data');
                    }
                });
        },

        loadContactsForMerge(excludeId) {
            fetch(`/contacts/merge/list?exclude_id=${excludeId}`)
                .then(response => response.json())
                .then(data => {
                    this.mergeContacts = data;
                    this.filteredMergeContacts = data;
                    this.mergeSearchQuery = '';
                })
                .catch(error => {
                    console.error('Error loading contacts:', error);
                    if (typeof window.showToast === 'function') {
                        window.showToast('Error loading contacts', 'error');
                    } else {
                        alert('Error loading contacts');
                    }
                });
        },

        filterMergeContacts() {
            if (!this.mergeSearchQuery || !this.mergeSearchQuery.trim()) {
                this.filteredMergeContacts = this.mergeContacts;
                return;
            }

            const query = this.mergeSearchQuery.toLowerCase();
            this.filteredMergeContacts = this.mergeContacts.filter(contact => {
                const fullName = `${contact.first_name} ${contact.last_name}`.toLowerCase();
                const email = (contact.email || '').toLowerCase();
                const phone = (contact.phone_number || '').toLowerCase();
                return fullName.includes(query) || email.includes(query) || phone.includes(query);
            });
        },

        selectMasterContact(contact) {
            this.mergeMasterContactId = contact.id;
            this.mergeMasterContact = contact;
        },

        showMergeConfirmation() {
            if (!this.mergeMasterContactId) {
                if (typeof window.showToast === 'function') {
                    window.showToast('Please select a master contact', 'error');
                } else {
                    alert('Please select a master contact');
                }
                return;
            }

            // Find the full master contact data
            const master = this.mergeContacts.find(c => c.id == this.mergeMasterContactId);
            if (master) {
                this.mergeMasterContact = master;
            }

            this.mergeOpen = false;
            this.mergeConfirmOpen = true;
        },

        async submitMerge() {
            if (!this.mergeMasterContactId || !this.mergeSecondaryContact.id) {
                if (typeof window.showToast === 'function') {
                    window.showToast('Please select a master contact', 'error');
                }
                return;
            }

            const formData = new FormData();
            formData.append('master_contact_id', this.mergeMasterContactId);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            formData.append('_token', csrfToken);

            const url = `{{ url('contacts') }}/${this.mergeSecondaryContact.id}/merge`;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Non-JSON response:', text.substring(0, 500));
                    if (typeof window.showToast === 'function') {
                        window.showToast('Something went wrong. Please try again.', 'error');
                    }
                    return;
                }

                const data = await response.json();

                if (data.success) {
                    if (typeof window.showToast === 'function') {
                        window.showToast(data.message, 'success');
                    }
                    this.mergeConfirmOpen = false;
                    this.mergeOpen = false;
                    this.mergeMasterContactId = null;
                    this.mergeSecondaryContact = {};
                    this.mergeMasterContact = {};
                    // Small delay to ensure modal closes before refreshing
                    setTimeout(() => {
                        this.refreshTable();
                    }, 100);
                } else {
                    if (typeof window.showToast === 'function') {
                        window.showToast(data.message || 'Something went wrong. Please try again.', 'error');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                if (typeof window.showToast === 'function') {
                    window.showToast('Something went wrong. Please try again.', 'error');
                }
            }
        },

        async submitContactForm() {
            const form = document.getElementById('contact-form');
            if (!form) return;

            this.isSubmitting = true;
            const formData = new FormData(form);
            
            // Add _method for PUT requests
            if (this.isEdit) {
                formData.append('_method', 'PUT');
            }
            
            const baseUrl = '{{ url('contacts') }}';
            const storeUrl = '{{ route('contacts.store') }}';
            const url = this.isEdit 
                ? `${baseUrl}/${this.editId}` 
                : storeUrl;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || formData.get('_token');

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Non-JSON response:', text.substring(0, 500));
                    this.showMessage('Something went wrong. Please try again.', 'error');
                    this.isSubmitting = false;
                    return;
                }

                const data = await response.json();

                if (data.success) {
                    this.showMessage(data.message, 'success');
                    this.fieldErrors = {};
                    this.open = false;
                    // Small delay to ensure modal closes before refreshing
                    setTimeout(() => {
                        this.refreshTable();
                    }, 100);
                } else {
                    // Parse and store field-specific errors
                    this.fieldErrors = {};
                    if (data.errors && typeof data.errors === 'object') {
                        Object.keys(data.errors).forEach(key => {
                            // Handle array fields like phone_numbers.0, emails.1, or custom_fields.1
                            if (Array.isArray(data.errors[key])) {
                                // For array fields, store each index error
                                data.errors[key].forEach((error, index) => {
                                    if (key.includes('.')) {
                                        // Already has index like phone_numbers.0
                                        this.fieldErrors[key] = error;
                                    } else {
                                        // Need to add index like phone_numbers.0
                                        this.fieldErrors[key + '.' + index] = error;
                                    }
                                });
                                // Also store general field error if exists
                                if (data.errors[key].length > 0) {
                                    this.fieldErrors[key] = data.errors[key][0];
                                }
                            } else {
                                this.fieldErrors[key] = data.errors[key];
                            }
                        });
                    }
                    // Show generic error message (not the detailed one)
                    this.showMessage(data.message || 'Something went wrong. Please try again.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showMessage('Something went wrong. Please try again.', 'error');
            } finally {
                this.isSubmitting = false;
            }
        },

        async deleteContact() {
            if (!this.deleteId) return;

            const formData = new FormData();
            formData.append('_method', 'DELETE');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            formData.append('_token', csrfToken);

            const baseUrl = '{{ url('contacts') }}';
            const url = `${baseUrl}/${this.deleteId}`;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Non-JSON response:', text.substring(0, 500));
                    this.showMessage('Something went wrong. Please try again.', 'error');
                    return;
                }

                const data = await response.json();

                if (data.success) {
                    this.showMessage(data.message, 'success');
                    this.deleteOpen = false;
                    this.deleteId = null;
                    // Small delay to ensure modal closes before refreshing
                    setTimeout(() => {
                        this.refreshTable();
                    }, 100);
                } else {
                    this.showMessage(data.message || 'Something went wrong. Please try again.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showMessage('Something went wrong. Please try again.', 'error');
            }
        },

        async refreshTable() {
            const list = document.getElementById('contacts-list');
            if (!list) {
                console.error('Table list element not found');
                return;
            }

            try {
                const indexUrl = '{{ route('contacts.index') }}';
                const response = await fetch(indexUrl, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                });

                if (response.ok) {
                    const html = await response.text();
                    // The AJAX endpoint returns only the rows partial, so we can use it directly
                    list.innerHTML = html;
                } else {
                    console.error('Failed to refresh table. Status:', response.status);
                    this.showMessage('Failed to refresh contact list.', 'error');
                }
            } catch (error) {
                console.error('Error refreshing table:', error);
                this.showMessage('Failed to refresh contact list.', 'error');
            }
        },

        showMessage(message, type = 'success', errors = null) {
            // Show toast notification
            if (typeof window.showToast === 'function') {
                let fullMessage = message;
                
                // Add validation errors if present
                if (errors && typeof errors === 'object') {
                    const errorList = [];
                    Object.keys(errors).forEach(key => {
                        if (Array.isArray(errors[key])) {
                            errors[key].forEach(error => {
                                errorList.push(error);
                            });
                        } else {
                            errorList.push(errors[key]);
                        }
                    });
                    if (errorList.length > 0) {
                        fullMessage += '\n' + errorList.join('\n');
                    }
                }
                
                window.showToast(fullMessage, type, 5000);
            }
            
            // Also show in message container (fallback)
            const container = document.getElementById('message-container');
            const content = document.getElementById('message-content');
            if (container && content) {
                const colorClass = type === 'success' 
                    ? 'text-green-600 dark:text-green-400' 
                    : 'text-red-600 dark:text-red-400';

                let messageHtml = '<div class="' + colorClass + '">' + message + '</div>';
                
                if (errors && typeof errors === 'object') {
                    messageHtml += '<ul class="mt-2 list-disc list-inside">';
                    Object.keys(errors).forEach(key => {
                        if (Array.isArray(errors[key])) {
                            errors[key].forEach(error => {
                                messageHtml += '<li class="' + colorClass + '">' + error + '</li>';
                            });
                        } else {
                            messageHtml += '<li class="' + colorClass + '">' + errors[key] + '</li>';
                        }
                    });
                    messageHtml += '</ul>';
                }

                content.innerHTML = messageHtml;
                container.classList.remove('hidden');

                // Auto-hide after 5 seconds
                setTimeout(() => {
                    container.classList.add('hidden');
                }, 5000);
            }
        }
    };
}
</script>

