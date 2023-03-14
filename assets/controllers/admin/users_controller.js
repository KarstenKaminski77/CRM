import { Controller } from '@hotwired/stimulus';

export default class extends Controller
{
    errorPage = '/admin/error';
    selected = [];

    connect()
    {
        let uri = window.location.pathname;
        let isUsersList = uri.match('/admin/users');

        if(isUsersList != null)
        {
            this.getUsers(1);
        }
    }

    onClickGetUsers(e)
    {
        e.preventDefault();

        this.getUsers(1);
    }

    onClickCreateNew(e)
    {
        e.preventDefault();

        let self = this;
        let clickedElement = e.currentTarget;

        $.ajax({
            async: "true",
            url: "/admin/user/form/new",
            type: 'POST',
            dataType: 'json',
            beforeSend: function ()
            {
                self.isLoading(true);
                window.scrollTo(0, 0);
                $('body').scrollTop($('body').prop("scrollHeight"));
            },
            complete: function(e)
            {
                if(e.status === 500)
                {
                    //window.location.href = self.errorPage;
                }
            },
            success: function (response)
            {
                $('.content-wrapper').empty().append(response);
                self.isLoading(false);

                window.history.pushState(null, "Fluid", '/admin/user/create');
            }
        });
    }

    onClickEditUser(e)
    {
        e.preventDefault();

        let self = this;
        let clickedElement = e.currentTarget;
        let userId = $(clickedElement).attr('data-user-id');

        $.ajax({
            async: "true",
            url: "/admin/user/form/update",
            type: 'POST',
            dataType: 'json',
            data: {
                'user-id': userId,
            },
            beforeSend: function ()
            {
                self.isLoading(true);
                window.scrollTo(0, 0);
                $('body').scrollTop($('body').prop("scrollHeight"));
            },
            complete: function(e)
            {
                if(e.status === 500)
                {
                    //window.location.href = self.errorPage;
                }
            },
            success: function (response)
            {
                $('.content-wrapper').empty().append(response);
                self.isLoading(false);

                window.history.pushState(null, "Fluid", '/admin/user/update');
            }
        });
    }

    onClickRoleField()
    {
        $('#roles_list').slideToggle(700);
    }

    onClickRoleSelect(e)
    {
        let clickedElement = e.currentTarget;
        let roleId = $(clickedElement).data('role-id');
        let role = $(clickedElement).attr('data-role');

        if($('#role_edit_field_'+ roleId).is(':hidden'))
        {
            let hiddenField = '<input type="hidden" name="roles[]" class="role_hidden" data-name="'+ role +'"';
            hiddenField += 'id="role_hidden_field_' + roleId + '" value="' + roleId + '" >';

            $('#roles_list').prepend(hiddenField);
            $(clickedElement).removeClass('role-select');
            $('#role_remove_' + roleId).show();

            let badge = this.getBadges('input[name="roles[]"]', 'role');

            $('#role').empty().append(badge);

            // Create array of selected ids
            let selected = this.selected;
            selected = [];

            if($('.role_hidden').length > 0)
            {
                $('.role_hidden').each(function ()
                {console.log(selected, $(clickedElement).attr('data-role'))
                    selected.push($(clickedElement).val());
                });
            }
        }
    }

    onClickRemoveRole(e)
    {
        e.preventDefault();

        let clickedElement = e.currentTarget;
        let roleId = $(clickedElement).data('role-id');

        $('#role_badge_'+ roleId).remove();
        $('#role_row_id_'+ roleId).addClass('role-select');
        $('#role_hidden_field_'+ roleId).remove();
        $('#role_remove_' + roleId).hide();

        // Remove from selected array
        let index = this.selected.indexOf(roleId);
        if (index >= 0)
        {
            this.selected.splice( index, 1 );
        }

        if($('.role_hidden').length == 0)
        {
            $('#role').append('Select a Role');
        };
    }

    onMouseOverRole(e)
    {
        let clickedElement = e.currentTarget;
        let roleId = $(clickedElement).data('role-id');

        $(clickedElement).find('.role-remove-icon').show()
    }

    onMouseOutRole(e)
    {
        let clickedElement = e.currentTarget;
        let roleId = $(clickedElement).data('role-id');

        $(clickedElement).find('.role-remove-icon').hide()
    }

    onSubmitUsersForm(e)
    {
        e.preventDefault();

        let self = this;
        let clickedElement = e.currentTarget;
        let firstName = $('#first_name').val();
        let lastName = $('#last_name').val();
        let email = $('#email').val();
        let isValid = true;
        let errorFirstName = $('#error_first_name');
        let errorLastName = $('#error_last_name');
        let errorEmail = $('#error_email');
        let btn = document.activeElement.getAttribute('name');

        errorFirstName.hide();
        errorLastName.hide();
        errorEmail.hide();

        if(firstName == '' || firstName == 'undefined'){

            errorFirstName.show();
            isValid = false;
        }

        if(lastName == '' || lastName == 'undefined'){

            errorLastName.show();
            isValid = false;
        }

        if(email == '' || email == 'undefined'){

            errorEmail.show();
            isValid = false;
        }

        if(btn == 'save_reset_password'){

            $('#reset_password').val(true);

        } else {

            $('#reset_password').val(false);
        }

        if(isValid)
        {
            let data = new FormData($(clickedElement)[0]);

            $.ajax({
                url: "/admin/user/new",
                type: 'POST',
                contentType: false,
                processData: false,
                cache: false,
                timeout: 600000,
                dataType: 'json',
                data: data,
                beforeSend: function ()
                {
                    self.isLoading(true);
                },
                success: function (response)
                {
                    self.isLoading(false);

                    if(btn == 'save_return')
                    {
                        self.getUsers(1);
                    }
                }
            });
        }
    }

    getUsers(pageId)
    {
        let self = this;

        $.ajax({
            async: "true",
            url: "/admin/users/list",
            type: 'POST',
            dataType: 'json',
            beforeSend: function ()
            {
                self.isLoading(true);
                window.scrollTo(0, 0);
                $('body').scrollTop($('body').prop("scrollHeight"));
            },
            complete: function(e)
            {
                if(e.status === 500)
                {
                    //window.location.href = self.errorPage;
                }
            },
            success: function (response)
            {
                $('.content-wrapper').empty().append(response);
                self.isLoading(false);

                window.history.pushState(null, "Fluid", '/admin/users');
            }
        });
    }

    onClickBackBtn(e)
    {
        e.preventDefault();

        this.getUsers(1);
    }

    getBadges(element, label){

        let arr = [];

        $(element).each(function() {

            let val = $(this).val();
            let name = $(this).data('name');

            arr.push({'id':val, 'name': name});
        });

        let badge = '';

        for(let i = 0; i < arr.length; i++){

            badge += '<span class="badge bg-disabled me-3 my-1" id="'+ label +'_badge_'+ arr[i].id +'">';
            badge += '<span  id="'+ label +'_badge_string_'+ arr[i].id +'">' + arr[i].name + '</span>';
            badge += '</span>';
        }

        return badge;
    }

    getFlash(flash)
    {
        $('#flash').addClass('alert-success').removeClass('alert-danger').addClass('alert').addClass('text-center');
        $('#flash').removeClass('users-flash').addClass('users-flash').empty().append(flash).removeClass('hidden');

        setTimeout(function()
        {
            $('#flash').removeClass('alert-success').removeClass('alert').removeClass('text-center');
            $('#flash').removeClass('users-flash').empty().addClass('hidden');
        }, 5000);
    }

    isLoading(status)
    {
        if(status)
        {
            $("div.spanner").addClass("show");
            $("div.overlay").addClass("show");

        }
        else
        {
            $("div.spanner").removeClass("show");
            $("div.overlay").removeClass("show");
        }
    }
}