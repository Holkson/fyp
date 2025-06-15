// Asnaf management functions
function openCreateDialog() {
    const dialog = document.getElementById('asnafDialog');
    dialog.classList.remove('hidden');
    resetForm();
}

function openEditDialog(id) {
    // Fetch asnaf data and populate form
    fetch(`api/asnaf.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            const dialog = document.getElementById('asnafDialog');
            dialog.classList.remove('hidden');
            
            // Get the form and populate it with the data
            const form = document.getElementById('asnafForm');
            form.querySelector('[name="id"]').value = data.id;
            form.querySelector('[name="name"]').value = data.name;
            form.querySelector('[name="ic"]').value = data.ic;
            form.querySelector('[name="phone"]').value = data.phone;
            form.querySelector('[name="address"]').value = data.address;
            form.querySelector('[name="tl"]').value = data.tl || '';
            form.querySelector('[name="occupation"]').value = data.occupation;
            form.querySelector('[name="status"]').value = data.status;
            form.querySelector('[name="total_dependent"]').value = data.total_dependent;
            form.querySelector('[name="dependent_names"]').value = data.dependent_names || '';
            form.querySelector('[name="problems"]').value = data.problems;

            // Update form title
            document.querySelector('#dialogTitle').textContent = 'Edit Asnaf Record';
            
            // Update submit button text
            document.querySelector('#submitButton').textContent = 'Save Changes';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load asnaf data');
        });
}

function closeDialog() {
    const dialog = document.getElementById('asnafDialog');
    dialog.classList.add('hidden');
    resetForm();
}

function resetForm() {
    const form = document.getElementById('asnafForm');
    form.reset();
    form.querySelector('[name="id"]').value = '';
    document.querySelector('#dialogTitle').textContent = 'Add New Asnaf';
    document.querySelector('#submitButton').textContent = 'Create';
}

function handleSubmit(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const id = formData.get('id');
    
    // Determine if this is a create or update operation
    const method = id ? 'PUT' : 'POST';
    const url = id ? `api/asnaf.php?id=${id}` : 'api/asnaf.php';
    
    fetch(url, {
        method: method,
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Operation failed');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Operation failed');
    });
}

function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this asnaf record? This action cannot be undone.')) {
        fetch(`api/asnaf.php?id=${id}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to delete record');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete record');
        });
    }
}