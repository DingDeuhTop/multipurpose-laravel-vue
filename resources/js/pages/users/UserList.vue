<script setup>
import { onMounted, reactive, ref, watch } from "vue"
import { Form, Field, useResetForm } from 'vee-validate'
import * as yup from 'yup';
import { useToastr } from '../../toastr.js'
import UserListItem from "./UserListItem.vue";
import { debounce } from 'lodash'
import { Bootstrap4Pagination } from 'laravel-vue-pagination';

const toastr = useToastr();
const users = ref({ 'data': [] });
const editing = ref(false);
const formValues = ref();
const form = ref(null);

// const form = reactive({
//     name: '',
//     email: '',
//     password: ''
// })

const getUsers = (page = 1) => {
    axios.get(`/api/users?page=${page}`, {
        params: {
            query: searchQuery.value
        }
    })
        .then((response) => {
            users.value = response.data;
            selectedUsers.value = [];
            selectAll.value = false;
        })
}

//same procedure as form
const createUserSchema = yup.object({
    name: yup.string().required(),
    email: yup.string().email().required(),
    password: yup.string().required().min(6)
});

const editUserSchema = yup.object({
    name: yup.string().required(),
    email: yup.string().email().required(),
    password: yup.string().when((password, schema) => {
        return password ? schema.required().min(6) : schema;
    }),
});


const createUser = (values, { resetForm, setErrors }) => {
    // console.log(values)
    axios.post('/api/users', values)
        .then((response) => {
            users.value.data.unshift(response.data)
            $('#userFormModal').modal('hide');
            resetForm();
            toastr.success('User created successfully')
        })
        .catch((error) => {
            if (error.response.data.errors) {
                setErrors(error.response.data.errors);
            }
        })
}

// const createUser = () => {
//     axios.post('/api/users', form)
//         .then((response) => {
//             users.value.unshift(response.data)
//             form.name = '';
//             form.email = '';
//             form.password = '';
//             $('#userFormModal').modal('hide');
//         });
// }

//make the editing modal false to turn new user
const addUser = () => {
    editing.value = false
    $('#userFormModal').modal('show');
}

const editUser = (user) => {
    // console.log(user)
    editing.value = true;
    form.value.resetForm(); //reset form after it's fill
    // this.$refs.form.resetForm()   vue2 command
    $('#userFormModal').modal('show');
    // formValues.value = user
    formValues.value = {
        id: user.id,
        name: user.name,
        email: user.email,
    }
}

const updateUser = (values, { setErrors }) => {
    // console.log(setErrors);
    axios.put('/api/users/' + formValues.value.id, values)
        .then((response) => {
            const index = users.value.findIndex(user => user.id === response.data.id);
            users.value[index] = response.data;
            $('#userFormModal').modal('hide');
            toastr.success('User updated successfully')
        }).catch((error) => {
            setErrors(error.response.data.errors);
            console.log(error);
        })
}

const handleSubmit = (values, actions) => {
    if (editing.value) {
        updateUser(values, actions)
    } else {
        createUser(values, actions);
    }
}


const searchQuery = ref(null);

// const search = () => {
//     axios.get('/api/users/search', {
//         params: {
//             query: searchQuery.value
//         }
//     })
//         .then(response => {
//             users.value = response.data;
//         })
//         .catch(error => {
//             console.log(error)
//         })
// }

const selectedUsers = ref([])

const toggleSelection = (user) => {
    const index = selectedUsers.value.indexOf(user.id);
    if (index === -1) {
        selectedUsers.value.push(user.id);
    } else {
        selectedUsers.value.splice(index, 1);
    }
    // selectedUsers.value.push(user.id);
    console.log(selectedUsers.value);
}


const userIdBeingDeleted = ref(null);

const confirmUserDeletion = (id) => {
    userIdBeingDeleted.value = id
    $('#deleteUserModal').modal('show')
}

const deleteUser = async () => {
    axios.delete(`/api/users/${userIdBeingDeleted.value}`)
        .then(() => {
            $('#deleteUserModal').modal('hide');
            // users.value = users.value.filter(user => user.id !== userIdBeingDeleted.value);
            toastr.success('User delete successfully!');
            users.value.data = users.value.data.filter(user => user.id !== userIdBeingDeleted.value);
        })
}


const bulkDelete = () => {
    axios.delete('/api/users', {
        data: {
            ids: selectedUsers.value
        }
    })
        .then(response => {
            users.value.data = users.value.data.filter(user => !selectedUsers.value.includes(user.id));
            selectedUsers.value = []
            selectAll.value = false
            toastr.success(response.data.message);
        })
}

const selectAll = ref(false);

const selectAllUsers = () => {
    if (selectAll.value) {
        selectedUsers.value = users.value.data.map(user => user.id);
    } else {
        selectedUsers.value = []
    }
    console.log(selectedUsers.value);
}


watch(searchQuery, debounce(() => {
    getUsers();
}, 300))


onMounted(async () => {
    getUsers()
    // toastr.info('Success');
})
</script>
<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Users</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">User</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


    <div class="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between">
                <div class="d-flex">
                    <button type="button" class="mb-2 btn btn-primary" @click="addUser">
                        <i class="fa fa-plus-circle mr-1"></i>
                        Add New User
                    </button>

                    <div v-if="selectedUsers.length > 0">
                        <button type="button" class="ml-2 mb-2 btn btn-danger" @click="bulkDelete"
                            @toggle-selection="toggleSelection">
                            <i class="fa fa-trash mr-1"></i>
                            Delete Selected
                        </button>
                        <span class="ml-2">Selected {{ selectedUsers.length }} users</span>
                    </div>

                </div>
                <div>
                    <input type="text" v-model="searchQuery" class="form-control" placeholder="Search...">
                    <!-- <button @click.prevent="search">Submit</button> -->
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th><input type="checkbox" v-model="selectAll" @change="selectAllUsers" /></th>
                                <th style="width: 10px">#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Registered Date</th>
                                <th>Role</th>
                                <th>Options</th>
                            </tr>
                        </thead>
                        <tbody v-if="users.data.length > 0">
                            <UserListItem v-for="(user, index) in users.data" :key="user.id" :user=user :index=index
                                @confirm-user-deletion="confirmUserDeletion" @edit-user="editUser"
                                :select-all="selectAll" />
                        </tbody>
                        <tbody v-else>
                            <tr>
                                <td class="text-center" colspan="6">No results found...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <Bootstrap4Pagination :data="users" @pagination-change-page="getUsers" />
        </div>
    </div>

    <div class="modal fade" id="userFormModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span v-if="editing">Edit User</span>
                        <span v-else>Add New User</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <Form ref="form" @submit="handleSubmit" :validation-schema="editing ? editUserSchema : createUserSchema"
                    v-slot="{ errors }" :initial-values="formValues">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <Field name="name" type="text" class="form-control " :class="{ 'is-invalid': errors.name }"
                                id="name" aria-describedby="nameHelp" placeholder="Enter full name" />
                            <span class="invalid-feedback">{{ errors.name }}</span>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <Field name="email" type="email" class="form-control " :class="{ 'is-invalid': errors.email }"
                                id="email" aria-describedby="nameHelp" placeholder="Enter full name" />
                            <span class="invalid-feedback">{{ errors.email }}</span>

                        </div>

                        <div class="form-group">
                            <label for="email">Password</label>
                            <Field name="password" type="password" class="form-control "
                                :class="{ 'is-invalid': errors.password }" id="password" aria-describedby="nameHelp"
                                placeholder="Enter password" />
                            <span class="invalid-feedback">{{ errors.password }}</span>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </Form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteUserModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span>Delete User</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">Are you sure, you want to delte this user ?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" @click.prevent="deleteUser">Delete</button>
                </div>
            </div>
        </div>
    </div>
</template>
