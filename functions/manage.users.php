<?php
// require '../config/config.php';
// require 'admin/role_access.php';

// Restrict access (only Admins and Health Workers can manage users)
// if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2) {
//     die("Access Denied");
// }
// ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../assets/user.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Manage Users</h1>
    
    <!-- <h2>Add User</h2> -->
    <button id="addUserBtn">+ Add User</button>

        <div id="userModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Add New User</h2>
                <form id="addUserForm">
                    <input type="hidden" id="user_id" name="user_id">
                    
                    <label>First Name:</label>
                    <input type="text" name="firstname" id="firstname" required>

                    <label>Last Name:</label>
                    <input type="text" name="lastname" id="lastname" required>

                    <label>Address:</label>
                    <input type="text" name="address" id="address" required>

                    <label>Username:</label>
                    <input type="text" name="username" id="username" required>

                    <label>Email:</label>
                    <input type="email" name="email" id="email" required>

                    <label>Password:</label>
                    <input type="password" name="password" id="password" required>

                    <label>Role:</label>
                    <select name="role_id" id="role_id">
                        <option value="1">Admin</option>
                        <!-- <option value="2">Health Worker</option> -->
                        <option value="3">User</option>
                    </select>

                    <label>Status:</label>
                    <select name="status" id="status">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>

                    <button type="submit" id="submitBtn">Add User</button>
                </form>
            </div>
        </div>



    <div id="message"></div> <!-- To display success/error messages -->

    <nav class="user-nav">
    <button onclick="filterUsers('all')">All</button>
    <button onclick="filterUsers('Active')">Active</button>
    <button onclick="filterUsers('Inactive')">Inactive</button>
    </nav>

    <nav class="user-nav">
    <button onclick="filterRoles('all')">All</button>
    <button onclick="filterRoles('1')">Admin</button>
    <!-- <button onclick="filterRoles('2')">Midwife</button> -->
    <!-- <button onclick="filterRoles('3')">Health Worker</button> -->
    </nav>




    <h2>Existing Users</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Address</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Date Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="userTable"></tbody>
    </table>
    
    <script>
        $(document).ready(function () {
            let editingUserId = null; 

            $("#addUserForm").submit(function (event) {
                event.preventDefault();

                let actionType = editingUserId ? "update" : "add";
                let formData = {
                    action: actionType,
                    firstname: $("#firstname").val(),
                    lastname: $("#lastname").val(),
                    address: $("#address").val(),
                    username: $("#username").val(),
                    email: $("#email").val(),
                    password: $("#password").val(),
                    role_id: $("#role_id").val(),
                    status: $("#status").val(), 
                };

                if (editingUserId) {
                    formData.user_id = editingUserId; 
                }

                $.ajax({
                    url: "functions/user.controller.php",
                    type: "POST",
                    data: formData,
                    dataType: "json",
                    success: function (response) {
                        if (response.success) {
                            $("#message").html(`<p style="color:green;">User ${actionType}d successfully!</p>`);
                            $("#addUserForm")[0].reset();
                            $("#submitBtn").text("Add User");
                            $("#password").prop("required", true).prop("disabled", false); // Enable password field again
                            editingUserId = null; // Reset editing state
                            loadUsers();
                        } else {
                            $("#message").html(`<p style="color:red;">Error: ${response.message}</p>`);
                        }
                    },
                    error: function () {
                        $("#message").html('<p style="color:red;">AJAX Request Failed!</p>');
                    }
                });
            });

            function loadUsers() {
                $.get("functions/user.controller.php", { action: "fetch" }, function (data) {
                    let users = JSON.parse(data);
                    let tableContent = "";
                    users.forEach(user => {
                        tableContent += `
                            <tr>
                                <td>${user.user_id}</td>
                                <td>${user.firstname}</td>
                                <td>${user.lastname}</td>
                                <td>${user.address}</td>
                                <td>${user.username}</td>
                                <td>${user.email}</td>
                                <td>${user.role_id}</td>
                                <td>${user.status}</td>
                                <td>${user.created_at}</td>
                                <td>
                                    <button class="editBtn" data-id="${user.user_id}" data-firstname="${user.firstname}"
                                            data-lastname="${user.lastname}" data-address="${user.address}" 
                                            data-username="${user.username}" data-email="${user.email}" 
                                            data-role="${user.role_id}" data-status="${user.status}">
                                        Edit
                                    </button>
                                    <button class="deleteBtn" data-id="${user.user_id}">Delete</button>
                                </td>
                            </tr>`;
                    });
                    $("#userTable").html(tableContent);
                });
            }

            // Edit button
            $(document).on("click", ".editBtn", function () {
                editingUserId = $(this).data("id"); // Get user ID
                $("#firstname").val($(this).data("firstname"));
                $("#lastname").val($(this).data("lastname"));
                $("#address").val($(this).data("address"));
                $("#username").val($(this).data("username"));
                $("#email").val($(this).data("email"));
                $("#role_id").val($(this).data("role"));
                $("#status").val($(this).data("status"));
                $("#password").prop("required", false).prop("disabled", true); // Disable password field when editing
                $("#submitBtn").text("Update User"); // Change button text
            });

            // Delete button event
            $(document).on("click", ".deleteBtn", function() {
                if (confirm("Are you sure you want to delete this user?")) {
                    let id = $(this).data("id");
                    $.post("/HumaCare/functions/user.controller.php", { delete_id: id, action: "delete" }, function(response) {
                        $("#message").html(response);
                        loadUsers(); // Reload the user list after deletion
                    });
                }
            });
            
            loadUsers(); // Load users on page load
        });

                // Filter Users by Status
            function filterUsers(status) {
                let rows = document.querySelectorAll("#userTable tr");
                rows.forEach(row => {
                    let userStatus = row.querySelector("td:nth-child(8)").innerText;
                    if (status === "all" || userStatus === status) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            }

               // Filter Users by Roles
               function filterRoles(role) {
                let rows = document.querySelectorAll("#userTable tr");
                rows.forEach(row => {
                    let userRole = row.querySelector("td:nth-child(7)").innerText; // Role is in the 7th column
                    if (role === "all" || userRole === role) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            }


            // Modal Open & Close for Add User Form
            let modal = document.getElementById("userModal");
            let btn = document.getElementById("addUserBtn");
            let span = document.getElementsByClassName("close")[0];

            btn.onclick = function () {
                modal.style.display = "flex";
            };

            span.onclick = function () {
                modal.style.display = "none";
            };

            window.onclick = function (event) {
                if (event.target === modal) {
                    modal.style.display = "none";
                }
            };


    </script>
    <style>

/* Page Header */
h1 {
    text-align: center;
    color: #136769;
}

/* Navigation & Filters */
.user-nav {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-bottom: 20px;
}

.user-nav button {
    background-color: #136769;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
}

.user-nav button:hover {
    background-color: #0f5252;
}

/* Table Styling */
table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 10px;
    box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
}

th, td {
    padding: 12px;
    text-align: left;
}

th {
    background-color: #136769;
    color: white;
    border-bottom: 2px solid #0f5252;
}

td {
    border-bottom: 1px solid #ddd;
}

/* Action Buttons */
button {
    padding: 8px 12px;
    border: none;
    cursor: pointer;
    border-radius: 4px;
}

.editBtn {
    background-color: #f4a261;
    color: white;
}

.deleteBtn {
    background-color: #e63946;
    color: white;
}

.editBtn:hover {
    background-color: #e67e22;
}

.deleteBtn:hover {
    background-color: #c92c2c;
}

/* Add User Button */
#addUserBtn {
    display: block;
    margin: 10px auto;
    background-color: #136769;
    color: white;
    padding: 12px 18px;
    border-radius: 6px;
    font-weight: bold;
}

#addUserBtn:hover {
    background-color: #0f5252;
}

/* Modal for Add User */
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background-color: white;
    padding: 20px;
    border-radius: 10px;
    width: 40%;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
}

.close {
    float: right;
    font-size: 20px;
    cursor: pointer;
}

.close:hover {
    color: red;
}

/* Form Inputs */
input, select {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

    </style>
</body>
</html>
