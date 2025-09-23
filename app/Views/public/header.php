<div class="bg-white sticky-top shadow-sm mb-4">
    <div class="container">
        <div class="row align-items-center">
            <!-- Left Side: Large Logo and Name (spans both lines) -->
            <div class="col-md-3">
                                        <a class="navbar-brand d-flex align-items-center" href="/" style="text-decoration: none;">
                            <img src="<?= base_url('public/logo.png') ?>" alt="Barind Post Logo" style="height:80px;width:auto;margin-right:15px;margin-top:2px;margin-bottom:2px;">
                            <div class="d-flex flex-column">
                                <span style="font-size:2.2rem; font-weight:700; color:#2c3e50; line-height:1.1;">বারিন্দ পোস্ট</span>
                                <span style="font-size:0.7rem; color:#fff; background-color:#dc3545; padding:2px 8px; border-radius:12px; font-weight:500; width:fit-content; margin-top:2px;">পরীক্ষামূলক সংস্করণ</span>
                            </div>
                        </a>
            </div>
            
            <!-- Right Side: Two-line Menu System -->
            <div class="col-md-9">
                <?php 
                // Separate categories into main menu, special, and dropdown
                $mainMenuItems = [];
                $specialCategories = [];
                $dropdownItems = [];
                
                foreach ($categories as $cat) {
                    if (isset($cat['isSpecial']) && $cat['isSpecial']) {
                        $specialCategories[] = $cat;
                    } else {
                        $mainMenuItems[] = $cat; // All non-special categories go to main menu
                    }
                }
                ?>
                
                <!-- First Line: Regular Menu Categories with Dynamic Overflow -->
                <div class="d-flex align-items-center mb-2" id="mainMenuContainer" style="overflow: hidden;">
                    <!-- Home menu item -->
                    <a class="nav-link fw-semibold me-3" href="/" style="color:#dc3545;">
                        <i class="fas fa-home"></i>
                    </a>
                    
                    <!-- All main menu items (will be dynamically shown/hidden) -->
                    <?php foreach ($mainMenuItems as $index => $cat): ?>
                        <a class="nav-link fw-semibold me-3 main-menu-item" href="/section/<?= esc($cat['slug']) ?>" style="color:#2c3e50; white-space: nowrap;" data-index="<?= $index ?>">
                            <?= esc($cat['name'], 'raw') ?>
                        </a>
                    <?php endforeach; ?>
                    
                    <!-- Dynamic overflow dropdown -->
                    <div class="dropdown" id="overflowDropdown" style="display: none; position: relative; z-index: 1050;">
                        <a class="nav-link dropdown-toggle fw-semibold" href="#" id="moreMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color:#2c3e50; white-space: nowrap;">
                            +
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="moreMenu" id="overflowMenu" style="z-index: 1051; position: absolute; top: 100%; left: 0; min-width: 200px;">
                            <!-- Overflow items will be populated by JavaScript -->
                        </ul>
                    </div>
                    
                </div>
                
                <!-- Second Line: Special Categories and Search -->
                <div class="d-flex flex-row w-100 justify-content-between align-items-center">
                    <!-- Special Categories -->
                    <div class="d-flex flex-wrap">
                        <?php if (!empty($specialCategories)): ?>
                            <?php foreach ($specialCategories as $cat): ?>
                                <a class="nav-link text-muted small me-3" href="/section/<?= esc($cat['slug']) ?>" style="font-size:0.85rem;">
                                    <?= esc($cat['name'], 'raw') ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Search Form -->
                    <form class="d-flex" method="get" action="/search">
                        <input class="form-control form-control-sm me-2" type="search" name="q" placeholder="সংবাদ খুঁজুন..." aria-label="Search" value="<?= esc($query ?? '', 'raw') ?>" style="width: 200px;">
                        <button class="btn btn-outline-danger btn-sm" type="submit">খুঁজুন</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function adjustMenuItems() {
        const container = document.getElementById('mainMenuContainer');
        const menuItems = container.querySelectorAll('.main-menu-item');
        const overflowDropdown = document.getElementById('overflowDropdown');
        const overflowMenu = document.getElementById('overflowMenu');
        
        if (!container || !menuItems.length) return;
        
        // Reset all items to visible first
        menuItems.forEach(item => {
            item.style.display = 'block';
        });
        overflowDropdown.style.display = 'none';
        overflowMenu.innerHTML = '';
        
        // Force a reflow to get accurate measurements
        container.offsetHeight;
        
        // Get the parent container (col-md-8) width
        const parentContainer = container.closest('.col-md-8');
        const containerWidth = parentContainer ? parentContainer.offsetWidth : container.offsetWidth;
        
        // Get fixed elements (home item only, no other dropdowns)
        const homeItem = container.querySelector('a[href="/"]');
        
        // Calculate space used by fixed elements
        let usedWidth = 0;
        if (homeItem) {
            usedWidth += homeItem.offsetWidth + 12; // 12px for margin
        }
        
        // Reserve space for overflow dropdown (only if we might need it)
        const overflowDropdownWidth = 50; // Approximate width of "+" dropdown
        const availableWidth = containerWidth - usedWidth - 10; // Buffer for safety
        
        // Calculate which items fit with more accurate logic
        let totalWidth = 0;
        const overflowItems = [];
        let needsOverflow = false;
        
        // First, try to fit all items without overflow dropdown
        let allItemsWidth = 0;
        menuItems.forEach(item => {
            allItemsWidth += item.offsetWidth + 12; // 12px for margin
        });
        
        // If all items fit, no overflow needed
        if (allItemsWidth <= availableWidth) {
            totalWidth = allItemsWidth;
        } else {
            // Need overflow - calculate which items fit with overflow dropdown
            needsOverflow = true;
            totalWidth = 0;
            
            menuItems.forEach((item, index) => {
                const itemWidth = item.offsetWidth + 12; // 12px for margin
                
                // Check if this item fits (accounting for overflow dropdown)
                const spaceNeeded = totalWidth + itemWidth + overflowDropdownWidth;
                
                if (spaceNeeded <= availableWidth) {
                    totalWidth += itemWidth;
                } else {
                    overflowItems.push({
                        element: item,
                        href: item.href,
                        text: item.textContent.trim(),
                        index: index
                    });
                    item.style.display = 'none';
                }
            });
        }
        
        // Show overflow dropdown if needed
        if (overflowItems.length > 0) {
            
            overflowDropdown.style.display = 'block';
            overflowMenu.innerHTML = '';
            
            overflowItems.forEach(item => {
                const li = document.createElement('li');
                const a = document.createElement('a');
                a.className = 'dropdown-item';
                a.href = item.href;
                a.textContent = item.text;
                li.appendChild(a);
                overflowMenu.appendChild(li);
            });
        } else {
            overflowDropdown.style.display = 'none';
        }
        
        
        
        
    }
    
    // Run on load and resize
    adjustMenuItems();
    window.addEventListener('resize', adjustMenuItems);
    
    // Run multiple times to ensure accuracy
    setTimeout(adjustMenuItems, 100);
    setTimeout(adjustMenuItems, 300);
    setTimeout(adjustMenuItems, 500);
    
    // Also run when images load (in case logo affects layout)
    window.addEventListener('load', adjustMenuItems);
    
    // Test dropdown functionality (temporary)
    setTimeout(() => {
        const overflowDropdown = document.getElementById('overflowDropdown');
        const overflowMenu = document.getElementById('overflowMenu');
        const moreMenuButton = document.getElementById('moreMenu');
        
        if (overflowDropdown && overflowMenu && moreMenuButton) {
            
            // Add some test items if none exist
            if (overflowMenu.children.length === 0) {
                const testItems = ['Test Item 1', 'Test Item 2', 'Test Item 3'];
                testItems.forEach(item => {
                    const li = document.createElement('li');
                    const a = document.createElement('a');
                    a.className = 'dropdown-item';
                    a.href = '#';
                    a.textContent = item;
                    li.appendChild(a);
                    overflowMenu.appendChild(li);
                });
            }
            
            // Force show dropdown for testing
            overflowDropdown.style.display = 'block';
            overflowMenu.style.display = 'block';
            overflowMenu.style.position = 'absolute';
            overflowMenu.style.zIndex = '9999';
            overflowMenu.style.backgroundColor = 'white';
            overflowMenu.style.border = '1px solid #ccc';
            overflowMenu.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
            overflowMenu.style.padding = '10px';
            overflowMenu.style.minWidth = '200px';
            overflowMenu.style.top = '100%';
            overflowMenu.style.left = '0';
            overflowMenu.style.listStyle = 'none';
            overflowMenu.style.margin = '0';
            
            // Make sure it's visible
            overflowMenu.style.visibility = 'visible';
            overflowMenu.style.opacity = '1';
            
            // Add manual click handler for testing
            moreMenuButton.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Manual click handler triggered');
                
                // Always show the dropdown when clicked
                overflowMenu.style.display = 'block';
                overflowMenu.style.visibility = 'visible';
                overflowMenu.style.opacity = '1';
                
                // Position it correctly below the + button
                const buttonRect = moreMenuButton.getBoundingClientRect();
                overflowMenu.style.backgroundColor = 'white';
                overflowMenu.style.color = '#333';
                overflowMenu.style.border = '1px solid #ccc';
                overflowMenu.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
                overflowMenu.style.position = 'fixed';
                overflowMenu.style.top = (buttonRect.bottom + 5) + 'px';
                overflowMenu.style.left = buttonRect.left + 'px';
                overflowMenu.style.zIndex = '99999';
                overflowMenu.style.minWidth = '200px';
                overflowMenu.style.padding = '10px';
                overflowMenu.style.listStyle = 'none';
                overflowMenu.style.margin = '0';
                
            });
            
        }
    }, 1000);
});
</script> 