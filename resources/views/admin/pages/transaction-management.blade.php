@include('admin.head')
@include('admin.sidebar')
@include('admin.navbar')

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Transaction Management</h4>
                    <div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bulkEditModal">
                            <i class="fas fa-edit"></i> Bulk Edit
                        </button>
                        <button class="btn btn-success" onclick="refreshTransactions()">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="userEmailFilter" placeholder="Filter by user email">
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" id="typeFilter">
                                <option value="">All Types</option>
                                <option value="deposit">Deposit</option>
                                <option value="withdrawal">Withdrawal</option>
                                <option value="profit">Profit</option>
                                <option value="bonus">Bonus</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" id="dateFromFilter" placeholder="From Date">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" id="dateToFilter" placeholder="To Date">
                        </div>
                        <div class="col-md-1">
                            <button class="btn btn-info" onclick="applyFilters()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Transactions Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="transactionsTable">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                    </th>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="transactionsTableBody">
                                <!-- Transactions will be loaded here via AJAX -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <nav aria-label="Transaction pagination">
                        <ul class="pagination justify-content-center" id="pagination">
                            <!-- Pagination will be loaded here -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Transaction Modal -->
<div class="modal fade" id="editTransactionModal" tabindex="-1" aria-labelledby="editTransactionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTransactionModalLabel">Edit Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTransactionForm">
                <div class="modal-body">
                    <input type="hidden" id="editTransactionId">
                    <div class="mb-3">
                        <label for="editAmount" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="editAmount" step="0.01" min="0">
                    </div>
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editDescription" rows="3" maxlength="500"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editReason" class="form-label">Reason for Edit</label>
                        <input type="text" class="form-control" id="editReason" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Edit Modal -->
<div class="modal fade" id="bulkEditModal" tabindex="-1" aria-labelledby="bulkEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkEditModalLabel">Bulk Edit Transactions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkEditForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bulkAmount" class="form-label">Amount (leave empty to keep current)</label>
                        <input type="number" class="form-control" id="bulkAmount" step="0.01" min="0">
                    </div>
                    <div class="mb-3">
                        <label for="bulkDescription" class="form-label">Description (leave empty to keep current)</label>
                        <textarea class="form-control" id="bulkDescription" rows="3" maxlength="500"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="bulkReason" class="form-label">Reason for Bulk Edit</label>
                        <input type="text" class="form-control" id="bulkReason" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Selected Transactions: <span id="selectedCount">0</span></label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Bulk Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Transaction Details Modal -->
<div class="modal fade" id="transactionDetailsModal" tabindex="-1" aria-labelledby="transactionDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionDetailsModalLabel">Transaction Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="transactionDetailsContent">
                <!-- Transaction details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let selectedTransactions = new Set();

// Load transactions on page load
document.addEventListener('DOMContentLoaded', function() {
    loadTransactions();
});

// Load transactions with filters
function loadTransactions(page = 1) {
    currentPage = page;
    const filters = {
        user_email: document.getElementById('userEmailFilter').value,
        type: document.getElementById('typeFilter').value,
        status: document.getElementById('statusFilter').value,
        date_from: document.getElementById('dateFromFilter').value,
        date_to: document.getElementById('dateToFilter').value,
        page: page,
        per_page: 15
    };

    fetch('/admin/transactions?' + new URLSearchParams(filters), {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            displayTransactions(data.data.data);
            displayPagination(data.data);
        } else {
            showAlert('Error loading transactions: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error loading transactions', 'danger');
    });
}

// Display transactions in table
function displayTransactions(transactions) {
    const tbody = document.getElementById('transactionsTableBody');
    tbody.innerHTML = '';

    transactions.forEach(transaction => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <input type="checkbox" class="transaction-checkbox" value="${transaction.id}" 
                       onchange="toggleTransactionSelection(${transaction.id})">
            </td>
            <td>${transaction.id}</td>
            <td>${transaction.user ? transaction.user.name : 'N/A'}</td>
            <td><span class="badge bg-${getTypeBadgeClass(transaction.type)}">${transaction.type}</span></td>
            <td>$${parseFloat(transaction.amount).toFixed(2)}</td>
            <td>${transaction.description || 'N/A'}</td>
            <td><span class="badge bg-${getStatusBadgeClass(transaction.status)}">${transaction.status}</span></td>
            <td>${new Date(transaction.created_at).toLocaleDateString()}</td>
            <td>
                <button class="btn btn-sm btn-info" onclick="viewTransaction(${transaction.id})">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-warning" onclick="editTransaction(${transaction.id})">
                    <i class="fas fa-edit"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Display pagination
function displayPagination(paginationData) {
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';

    if (paginationData.last_page > 1) {
        // Previous button
        if (paginationData.current_page > 1) {
            const prevLi = document.createElement('li');
            prevLi.className = 'page-item';
            prevLi.innerHTML = `<a class="page-link" href="#" onclick="loadTransactions(${paginationData.current_page - 1})">Previous</a>`;
            pagination.appendChild(prevLi);
        }

        // Page numbers
        for (let i = 1; i <= paginationData.last_page; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === paginationData.current_page ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#" onclick="loadTransactions(${i})">${i}</a>`;
            pagination.appendChild(li);
        }

        // Next button
        if (paginationData.current_page < paginationData.last_page) {
            const nextLi = document.createElement('li');
            nextLi.className = 'page-item';
            nextLi.innerHTML = `<a class="page-link" href="#" onclick="loadTransactions(${paginationData.current_page + 1})">Next</a>`;
            pagination.appendChild(nextLi);
        }
    }
}

// Apply filters
function applyFilters() {
    loadTransactions(1);
}

// Refresh transactions
function refreshTransactions() {
    loadTransactions(currentPage);
}

// Toggle select all
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.transaction-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
        if (selectAll.checked) {
            selectedTransactions.add(parseInt(checkbox.value));
        } else {
            selectedTransactions.delete(parseInt(checkbox.value));
        }
    });
    
    updateSelectedCount();
}

// Toggle individual transaction selection
function toggleTransactionSelection(transactionId) {
    const checkbox = document.querySelector(`input[value="${transactionId}"]`);
    if (checkbox.checked) {
        selectedTransactions.add(transactionId);
    } else {
        selectedTransactions.delete(transactionId);
    }
    updateSelectedCount();
}

// Update selected count
function updateSelectedCount() {
    document.getElementById('selectedCount').textContent = selectedTransactions.size;
}

// View transaction details
function viewTransaction(transactionId) {
    fetch(`/admin/transactions/${transactionId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            displayTransactionDetails(data.data);
        } else {
            showAlert('Error loading transaction details: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error loading transaction details', 'danger');
    });
}

// Display transaction details
function displayTransactionDetails(data) {
    const content = document.getElementById('transactionDetailsContent');
    content.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>Transaction Information</h6>
                <p><strong>ID:</strong> ${data.transaction.id}</p>
                <p><strong>User:</strong> ${data.transaction.user ? data.transaction.user.name : 'N/A'}</p>
                <p><strong>Email:</strong> ${data.transaction.user ? data.transaction.user.email : 'N/A'}</p>
                <p><strong>Type:</strong> ${data.transaction.type}</p>
                <p><strong>Amount:</strong> $${parseFloat(data.transaction.amount).toFixed(2)}</p>
                <p><strong>Status:</strong> ${data.transaction.status}</p>
                <p><strong>Description:</strong> ${data.transaction.description || 'N/A'}</p>
                <p><strong>Date:</strong> ${new Date(data.transaction.created_at).toLocaleString()}</p>
            </div>
            <div class="col-md-6">
                <h6>Edit History</h6>
                <div style="max-height: 300px; overflow-y: auto;">
                    ${data.edit_history.length > 0 ? 
                        data.edit_history.map(edit => `
                            <div class="border-bottom pb-2 mb-2">
                                <small class="text-muted">${new Date(edit.created_at).toLocaleString()}</small><br>
                                <strong>${edit.field_name}:</strong> ${edit.old_value} → ${edit.new_value}<br>
                                <strong>Reason:</strong> ${edit.reason}
                            </div>
                        `).join('') : 
                        '<p class="text-muted">No edit history</p>'
                    }
                </div>
            </div>
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('transactionDetailsModal')).show();
}

// Edit transaction
function editTransaction(transactionId) {
    // Load transaction data into edit form
    fetch(`/admin/transactions/${transactionId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            document.getElementById('editTransactionId').value = transactionId;
            document.getElementById('editAmount').value = data.transaction.amount;
            document.getElementById('editDescription').value = data.transaction.description || '';
            document.getElementById('editReason').value = '';
            
            new bootstrap.Modal(document.getElementById('editTransactionModal')).show();
        } else {
            showAlert('Error loading transaction: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error loading transaction', 'danger');
    });
}

// Handle edit form submission
document.getElementById('editTransactionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const transactionId = document.getElementById('editTransactionId').value;
    const formData = {
        amount: document.getElementById('editAmount').value,
        description: document.getElementById('editDescription').value,
        reason: document.getElementById('editReason').value
    };

    fetch(`/admin/transactions/${transactionId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            showAlert('Transaction updated successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('editTransactionModal')).hide();
            loadTransactions(currentPage);
        } else {
            showAlert('Error updating transaction: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error updating transaction', 'danger');
    });
});

// Handle bulk edit form submission
document.getElementById('bulkEditForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (selectedTransactions.size === 0) {
        showAlert('Please select at least one transaction', 'warning');
        return;
    }

    const formData = {
        transaction_ids: Array.from(selectedTransactions),
        amount: document.getElementById('bulkAmount').value,
        description: document.getElementById('bulkDescription').value,
        reason: document.getElementById('bulkReason').value
    };

    fetch('/admin/transactions/bulk-update', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            showAlert(`Bulk update completed. ${data.data.updated_count} transactions updated successfully`, 'success');
            bootstrap.Modal.getInstance(document.getElementById('bulkEditModal')).hide();
            selectedTransactions.clear();
            document.getElementById('selectAll').checked = false;
            updateSelectedCount();
            loadTransactions(currentPage);
        } else {
            showAlert('Error in bulk update: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error in bulk update', 'danger');
    });
});

// Utility functions
function getTypeBadgeClass(type) {
    const classes = {
        'deposit': 'success',
        'withdrawal': 'warning',
        'profit': 'info',
        'bonus': 'primary'
    };
    return classes[type] || 'secondary';
}

function getStatusBadgeClass(status) {
    const classes = {
        'pending': 'warning',
        'completed': 'success',
        'failed': 'danger'
    };
    return classes[status] || 'secondary';
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.insertBefore(alertDiv, document.body.firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
</script>

@include('admin.footer')
