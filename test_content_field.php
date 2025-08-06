<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Field Test - Barind Post</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #ckeditor-container {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            min-height: 400px;
        }
        #content {
            display: none !important;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1 class="mb-4">Content Field Test</h1>
                
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Content</label>
                        <div id="ckeditor-container">
                            <textarea name="content" id="content" class="form-control" rows="15" required></textarea>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Language</label>
                        <select name="language" class="form-select" required>
                            <option value="bn" selected>Bangla</option>
                            <option value="en">English</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Test Submit</button>
                </form>
            </div>
        </div>
    </div>

    <!-- CKEditor Script -->
    <script src="https://cdn.ckeditor.com/ckeditor5/40.2.0/classic/ckeditor.js"></script>
    <script>
        // Tweet Embed Plugin for CKEditor
        class TweetEmbedPlugin {
            constructor(editor) {
                this.editor = editor;
                this._defineSchema();
                this._defineConverters();
                this._addToolbarButton();
            }

            _defineSchema() {
                const schema = this.editor.model.schema;
                
                schema.register('tweetEmbed', {
                    isObject: true,
                    allowWhere: '$block',
                    allowAttributes: ['tweetUrl', 'tweetId']
                });
            }

            _defineConverters() {
                const conversion = this.editor.conversion;
                
                // Downcast converter
                conversion.for('downcast').elementToElement({
                    model: 'tweetEmbed',
                    view: (modelElement, viewWriter) => {
                        const tweetUrl = modelElement.getAttribute('tweetUrl');
                        const tweetId = modelElement.getAttribute('tweetId');
                        
                        const tweetContainer = viewWriter.createContainerElement('div', {
                            class: 'tweet-embed-container',
                            'data-tweet-url': tweetUrl,
                            'data-tweet-id': tweetId
                        });
                        
                        const tweetEmbed = viewWriter.createRawElement('div', {
                            class: 'tweet-embed-placeholder'
                        }, function(domElement) {
                            // Load Twitter widget script if not already loaded
                            if (!window.twttr) {
                                const script = document.createElement('script');
                                script.src = 'https://platform.twitter.com/widgets.js';
                                script.charset = 'utf-8';
                                document.head.appendChild(script);
                            }
                            
                            // Create tweet embed
                            setTimeout(() => {
                                if (window.twttr && tweetId) {
                                    window.twttr.widgets.createTweet(tweetId, domElement, {
                                        conversation: 'none',
                                        cards: 'visible',
                                        theme: 'light'
                                    });
                                } else if (tweetUrl) {
                                    // Fallback for URL-based embedding
                                    domElement.innerHTML = `
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
                            }, 100);
                        });
                        
                        return tweetContainer;
                    }
                });
                
                // Upcast converter
                conversion.for('upcast').elementToElement({
                    model: 'tweetEmbed',
                    view: {
                        name: 'div',
                        classes: 'tweet-embed-container'
                    }
                });
            }

            _addToolbarButton() {
                const editor = this.editor;
                
                editor.ui.componentFactory.add('tweetEmbed', locale => {
                    const view = new editor.ui.ButtonView(locale);
                    
                    view.set({
                        label: 'Embed Tweet',
                        icon: '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>',
                        tooltip: true
                    });
                    
                    view.on('execute', () => {
                        this._showTweetDialog();
                    });
                    
                    return view;
                });
            }

            _showTweetDialog() {
                const editor = this.editor;
                
                // Create modal dialog
                const modal = document.createElement('div');
                modal.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.5);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 9999;
                `;
                
                const dialog = document.createElement('div');
                dialog.style.cssText = `
                    background: white;
                    padding: 30px;
                    border-radius: 8px;
                    max-width: 500px;
                    width: 90%;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                `;
                
                dialog.innerHTML = `
                    <h3 style="margin-top: 0; color: #333;">Embed Tweet</h3>
                    <p style="color: #666; margin-bottom: 20px;">Enter a Twitter URL or Tweet ID to embed:</p>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Tweet URL or ID:</label>
                        <input type="text" id="tweetInput" placeholder="https://twitter.com/user/status/123456789 or 123456789" 
                               style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                    
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button id="cancelTweet" style="padding: 8px 16px; border: 1px solid #ddd; background: #f8f9fa; border-radius: 4px; cursor: pointer;">Cancel</button>
                        <button id="embedTweet" style="padding: 8px 16px; background: #1da1f2; color: white; border: none; border-radius: 4px; cursor: pointer;">Embed Tweet</button>
                    </div>
                `;
                
                modal.appendChild(dialog);
                document.body.appendChild(modal);
                
                const input = dialog.querySelector('#tweetInput');
                const embedBtn = dialog.querySelector('#embedTweet');
                const cancelBtn = dialog.querySelector('#cancelTweet');
                
                // Focus input
                input.focus();
                
                // Handle embed button
                embedBtn.addEventListener('click', () => {
                    const value = input.value.trim();
                    if (value) {
                        this._insertTweet(value);
                        document.body.removeChild(modal);
                    } else {
                        alert('Please enter a valid Twitter URL or Tweet ID');
                    }
                });
                
                // Handle cancel button
                cancelBtn.addEventListener('click', () => {
                    document.body.removeChild(modal);
                });
                
                // Handle Enter key
                input.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        embedBtn.click();
                    }
                });
                
                // Handle Escape key
                modal.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        document.body.removeChild(modal);
                    }
                });
                
                // Close on outside click
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        document.body.removeChild(modal);
                    }
                });
            }

            _insertTweet(value) {
                const editor = this.editor;
                
                // Extract tweet ID from URL or use as ID
                let tweetId = null;
                let tweetUrl = value;
                
                // Check if it's a URL
                if (value.includes('twitter.com') || value.includes('x.com')) {
                    const urlMatch = value.match(/\/status\/(\d+)/);
                    if (urlMatch) {
                        tweetId = urlMatch[1];
                    }
                } else {
                    // Assume it's a tweet ID
                    tweetId = value;
                }
                
                editor.model.change(writer => {
                    const tweetElement = writer.createElement('tweetEmbed', {
                        tweetUrl: tweetUrl,
                        tweetId: tweetId
                    });
                    
                    editor.model.insertContent(tweetElement);
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            ClassicEditor.create(document.querySelector('#content'), {
                toolbar: [
                    'heading', '|',
                    'bold', 'italic', 'underline', 'strikethrough', '|',
                    'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|',
                    'alignment', '|',
                    'link', '|',
                    'bulletedList', 'numberedList', '|',
                    'outdent', 'indent', '|',
                    'blockQuote', 'code', 'codeBlock', '|',
                    'insertTable', 'tableColumn', 'tableRow', 'mergeTableCells', '|',
                    'imageUpload', 'imageStyle:full', 'imageStyle:side', '|',
                    'horizontalLine', 'specialCharacters', '|',
                    'tweetEmbed', '|',
                    'undo', 'redo'
                ],
                simpleUpload: {
                    uploadUrl: '/image-upload/upload',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                },
                // Custom plugins
                extraPlugins: [TweetEmbedPlugin]
            })
            .then(editor => {
                console.log('CKEditor initialized successfully');
                
                // Store editor instance globally
                window.ckEditorInstance = editor;
                
                // Sync content with textarea
                editor.model.document.on('change:data', () => {
                    const data = editor.getData();
                    document.querySelector('#content').value = data;
                });
                
                // Form submission
                const form = document.querySelector('form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        const editorData = editor.getData();
                        document.querySelector('#content').value = editorData;
                        
                        if (!editorData.trim()) {
                            e.preventDefault();
                            alert('Please provide content.');
                            editor.focus();
                            return false;
                        }
                        
                        console.log('Form submitted with content:', editorData);
                        console.log('Language selected:', document.querySelector('select[name="language"]').value);
                    });
                }
            })
            .catch(error => {
                console.error('CKEditor initialization failed:', error);
            });
        });
    </script>
</body>
</html> 