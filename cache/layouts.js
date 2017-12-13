if (typeof gc == 'undefined') {gc = {app: {}};}
gc.app.layoutdefs = {
    "Users": {
        "detail": {
            "fields": [
                "user_name",
                "first_name",
                "last_name",
                "email_address",
                "phone_number",
                "user_type",
                "movies"
            ]
        },
        "list": {
            "fields": [
                "user_name",
                "first_name",
                "last_name",
                "email_address",
                "user_type"
            ]
        },
        "propdefs": {
            "id": {
                "name": "id",
                "source": "db",
                "datatype": "string",
                "fieldtype": "hidden",
                "defaultvalue": null,
                "required": true,
                "label": "id",
                "len": "16",
                "searchable": true,
                "isPrimary": true
            },
            "user_name": {
                "name": "user_name",
                "source": "db",
                "datatype": "string",
                "fieldtype": "text",
                "defaultvalue": "",
                "required": true,
                "label": "Login",
                "len": "64",
                "searchable": true,
                "tool-tip": "Your Login Name"
            },
            "first_name": {
                "name": "first_name",
                "source": "db",
                "datatype": "string",
                "fieldtype": "text",
                "defaultvalue": "",
                "required": false,
                "label": "Fist Name",
                "len": 64,
                "searchable": true,
                "tool-tip": "The user's first name"
            },
            "last_name": {
                "name": "last_name",
                "source": "db",
                "datatype": "string",
                "fieldtype": "text",
                "defaultvalue": "",
                "required": false,
                "label": "Last Name",
                "len": 64,
                "searchable": true,
                "tool-tip": "The user's last name"
            },
            "email_address": {
                "name": "email_address",
                "source": "db",
                "datatype": "string",
                "fieldtype": "text",
                "defaultvalue": "",
                "required": false,
                "label": "Email Address",
                "len": 255,
                "searchable": true,
                "tool-tip": "The user's email address"
            },
            "password_hash": {
                "name": "password_hash",
                "source": "db",
                "datatype": "string",
                "fieldtype": "password",
                "defaultvalue": "",
                "required": true,
                "label": "Password",
                "len": 64,
                "searchable": false,
                "tool-tip": "The user's password"
            },
            "phone_number": {
                "name": "phone_number",
                "source": "db",
                "datatype": "string",
                "fieldtype": "text",
                "defaultvalue": "",
                "required": false,
                "label": "Phone Number",
                "len": 18,
                "searchable": true,
                "tool-tip": "The user's phone number"
            },
            "user_type": {
                "name": "user_type",
                "source": "db",
                "datatype": "string",
                "fieldtype": "select",
                "defaultvalue": "regular",
                "required": true,
                "label": "User Type",
                "len": 64,
                "searchable": false,
                "tool-tip": "Type of user.",
                "options": {
                    "regular": "Regular User",
                    "admin": "Administrative User",
                    "guest": "Guest User"
                }
            },
            "movies": {
                "name": "movies",
                "source": "non-db",
                "datatype": "relationship",
                "relationship": "Users_Movies",
                "multiple": true,
                "fieldtype": "select",
                "label": "Movies",
                "tool-tip": "Movie for this users",
                "options": []
            }
        }
    },
    "Movies": {
        "detail": {
            "fields": [
                "title",
                "description",
                "tagline",
                "release_date"
            ]
        },
        "list": {
            "fields": [
                "title",
                "tagline",
                "release_date"
            ]
        },
        "propdefs": {
            "id": {
                "name": "id",
                "source": "db",
                "datatype": "string",
                "fieldtype": "hidden",
                "defaultvalue": null,
                "required": true,
                "label": "id",
                "len": "16",
                "searchable": true,
                "isPrimary": true
            },
            "title": {
                "name": "title",
                "source": "db",
                "datatype": "string",
                "fieldtype": "text",
                "defaultvalue": null,
                "required": true,
                "label": "Title",
                "len": "255",
                "searchable": true
            },
            "description": {
                "name": "description",
                "source": "db",
                "datatype": "string",
                "fieldtype": "text",
                "defaultvalue": null,
                "required": false,
                "label": "Synopsis",
                "len": "65535",
                "searchable": true
            },
            "tagline": {
                "name": "tagline",
                "source": "db",
                "datatype": "string",
                "fieldtype": "text",
                "defaultvalue": null,
                "required": false,
                "label": "Tagline",
                "len": "1000",
                "searchable": true
            },
            "release_date": {
                "name": "release_date",
                "source": "db",
                "datatype": "date",
                "fieldtype": "date",
                "defaultvalue": null,
                "required": false,
                "label": "Release Date",
                "len": "1000",
                "searchable": true
            }
        }
    }
}
