jQuery(document).ready(function($) {
    $('#githubUserForm').on('submit', function(e) {
        e.preventDefault();
        const username = $('#githubUsername').val();
        $.ajax({
            url: githubIntegration.ajax_url,
            method: 'POST',
            data: {
                action: 'get_github_user_data',
                username: username
            },
            success: function(response) {
                if (response.success) {
                    displayUserProfile(response.data.profile);
                    displayUserRepos(response.data.repos);
                } else {
                    showError(response.data);
                }
            },
            error: function() {
                showError('Error fetching data from GitHub.');
            }
        });
    });

    function displayUserProfile(profile) {
        $('#githubUserProfile').html(`
            <h2>${profile.name}</h2>
            <p><strong>Username:</strong> ${profile.login}</p>
            <p><strong>Bio:</strong> ${profile.bio}</p>
            <p><strong>Location:</strong> ${profile.location}</p>
            <p><strong>Public Repos:</strong> ${profile.public_repos}</p>
        `);
    }

    function displayUserRepos(repos) {
        const reposHtml = repos.map(repo => `
            <div class="user-repo">
                <span>${repo.name}</span>
                <a href="${repo.html_url}" target="_blank">View Repo</a>
            </div>
        `).join('');
        $('#githubUserRepos').html('<h2>Repositories</h2>' + reposHtml);
    }

    function showError(error) {
        $('#githubUserProfile').html(`<p class="error">${error}</p>`);
        $('#githubUserRepos').empty();
    }
});