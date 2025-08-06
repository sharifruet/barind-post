<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tweet Embed Test - Barind Post</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .tweet-embed-container {
            margin: 20px 0;
            text-align: center;
            max-width: 100%;
            overflow: hidden;
        }
        .tweet-embed-placeholder {
            display: inline-block;
            max-width: 100%;
            min-height: 200px;
            background: #f8f9fa;
            border: 1px solid #e1e8ed;
            border-radius: 8px;
            padding: 20px;
            margin: 10px 0;
        }
        .tweet-embed-placeholder:empty::before {
            content: "Loading tweet...";
            color: #657786;
            font-style: italic;
        }
        .twitter-tweet {
            margin: 0 auto !important;
            max-width: 100% !important;
        }
        .twitter-tweet-rendered {
            max-width: 100% !important;
            width: auto !important;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1 class="mb-4">Tweet Embed Test</h1>
                
                <div class="alert alert-info">
                    <h5>How to use Tweet Embed in CKEditor:</h5>
                    <ol>
                        <li>Click the Twitter icon (🐦) in the CKEditor toolbar</li>
                        <li>Enter a Twitter URL (e.g., https://twitter.com/user/status/123456789)</li>
                        <li>Or enter just the Tweet ID (e.g., 123456789)</li>
                        <li>Click "Embed Tweet"</li>
                    </ol>
                </div>

                <h3>Example Embedded Tweets:</h3>
                
                <!-- Example 1: Using Tweet ID -->
                <div class="tweet-embed-container">
                    <div class="tweet-embed-placeholder" data-tweet-id="1234567890123456789">
                        <!-- Tweet will be loaded here -->
                    </div>
                </div>

                <!-- Example 2: Using Twitter URL -->
                <div class="tweet-embed-container">
                    <div class="tweet-embed-placeholder" data-tweet-url="https://twitter.com/elonmusk/status/1234567890123456789">
                        <!-- Tweet will be loaded here -->
                    </div>
                </div>

                <div class="mt-4">
                    <a href="/admin/news/create" class="btn btn-primary">Go to News Editor</a>
                    <a href="/admin/news" class="btn btn-secondary">View All News</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Twitter Widget Script -->
    <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
    
    <script>
        // Load tweets when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const tweetContainers = document.querySelectorAll('.tweet-embed-placeholder');
            
            tweetContainers.forEach(container => {
                const tweetId = container.getAttribute('data-tweet-id');
                const tweetUrl = container.getAttribute('data-tweet-url');
                
                if (tweetId && window.twttr) {
                    window.twttr.widgets.createTweet(tweetId, container, {
                        conversation: 'none',
                        cards: 'visible',
                        theme: 'light'
                    });
                } else if (tweetUrl) {
                    // Fallback for URL-based embedding
                    container.innerHTML = `
                        <div style="border: 1px solid #e1e8ed; border-radius: 8px; padding: 15px; background: #f8f9fa;">
                            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                                <div style="width: 48px; height: 48px; background: #1da1f2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div style="font-weight: bold; color: #14171a;">Twitter</div>
                                    <div style="color: #657786; font-size: 14px;">@twitter</div>
                                </div>
                            </div>
                            <div style="color: #14171a; line-height: 1.4;">
                                <a href="${tweetUrl}" target="_blank" style="color: #1da1f2; text-decoration: none;">
                                    View Tweet on Twitter
                                </a>
                            </div>
                        </div>
                    `;
                }
            });
        });
    </script>
</body>
</html> 