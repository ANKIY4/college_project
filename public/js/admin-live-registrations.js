(function () {
    var panel = document.getElementById('live-registrations-panel');
    if (!panel) {
        return;
    }

    var feedUrl = panel.getAttribute('data-feed-url');
    if (!feedUrl) {
        return;
    }

    var totalRegistrationsElement = document.getElementById('live-total-registrations');
    var perEventCountsElement = document.getElementById('live-per-event-counts');
    var recentRegistrationsBody = document.getElementById('live-recent-registrations-body');
    var updatedAtElement = document.getElementById('live-registrations-updated');
    var statusElement = document.getElementById('live-registrations-status');
    var pollingIntervalMs = 5000;
    var requestInFlight = false;

    function escapeHtml(value) {
        if (value === null || value === undefined) {
            return '';
        }

        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function setStatus(message) {
        if (!statusElement) {
            return;
        }

        statusElement.textContent = message;
    }

    function renderPerEventCounts(perEventCounts) {
        if (!perEventCountsElement) {
            return;
        }

        if (!Array.isArray(perEventCounts) || perEventCounts.length === 0) {
            perEventCountsElement.innerHTML = '<div>No event counts available yet.</div>';
            return;
        }

        perEventCountsElement.innerHTML = perEventCounts.map(function (eventCount) {
            var title = escapeHtml(eventCount.event_title || 'Untitled event');
            var count = Number(eventCount.registration_count || 0);

            return '<div><strong>' + title + '</strong><br><span class="meta">' + count + ' registrations</span></div>';
        }).join('');
    }

    function renderRecentRegistrations(recentRegistrations) {
        if (!recentRegistrationsBody) {
            return;
        }

        if (!Array.isArray(recentRegistrations) || recentRegistrations.length === 0) {
            recentRegistrationsBody.innerHTML = '<tr><td colspan="5" class="meta">No recent registrations found.</td></tr>';
            return;
        }

        recentRegistrationsBody.innerHTML = recentRegistrations.map(function (registration) {
            return '<tr>' +
                '<td>' + escapeHtml(registration.full_name || '') + '</td>' +
                '<td>' + escapeHtml(registration.email || '') + '</td>' +
                '<td>' + escapeHtml(registration.college || '') + '</td>' +
                '<td>' + escapeHtml(registration.event_title || '') + '</td>' +
                '<td>' + escapeHtml(registration.submitted_at_local || '') + '</td>' +
                '</tr>';
        }).join('');
    }

    function updateLastUpdated(timestamp) {
        if (!updatedAtElement) {
            return;
        }

        updatedAtElement.textContent = 'Last updated: ' + (timestamp || new Date().toLocaleTimeString());
    }

    function fetchLiveRegistrations() {
        if (requestInFlight) {
            return;
        }

        requestInFlight = true;

        fetch(feedUrl, {
            method: 'GET',
            cache: 'no-store',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
            },
        })
            .then(function (response) {
                if (response.ok) {
                    return response.json();
                }

                return response.json()
                    .catch(function () {
                        return {};
                    })
                    .then(function (payload) {
                        var errorMessage = payload.message ? String(payload.message) : 'Request failed (' + response.status + ').';
                        throw new Error(errorMessage);
                    });
            })
            .then(function (payload) {
                if (!payload || payload.ok === false) {
                    throw new Error(payload && payload.message ? String(payload.message) : 'Unexpected response from server.');
                }

                if (totalRegistrationsElement) {
                    totalRegistrationsElement.textContent = Number(payload.total_registrations || 0);
                }

                renderPerEventCounts(payload.per_event_counts || []);
                renderRecentRegistrations(payload.recent_registrations || []);
                updateLastUpdated(payload.generated_at_local || '');
                setStatus('Live feed active.');
            })
            .catch(function (error) {
                setStatus('Unable to refresh live registrations: ' + error.message);
            })
            .then(function () {
                requestInFlight = false;
            });
    }

    fetchLiveRegistrations();
    window.setInterval(fetchLiveRegistrations, pollingIntervalMs);
})();
