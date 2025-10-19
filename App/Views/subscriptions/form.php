<div>
    <h2>
        Create Subscription
    </h2>
    <?php if (!empty($errors)) { ?>
        <div class="error"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
    <?php } ?>

    <form method="post" action="/?route=subscriptions.create">
        <div>
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($old['name'] ?? '') ?>">
        </div>
        <div>
            <label for="price">Price</label>
            <input type="text" id="price" name="price" value="<?php echo htmlspecialchars($old['price'] ?? '') ?>">
        </div>
        <div>
            <label for="includes_physical_magazine">Includes physical magazine?</label>
            <input type="checkbox" id="includes_physical_magazine" name="includes_physical_magazine" value="1"
                    <?php echo !empty($old['includes_physical_magazine']) ? 'checked' : '' ?>>
        </div>
        <div>
            <label for="description">Description</label>
            <textarea id="description"
                      name="description"><?php echo htmlspecialchars($old['description'] ?? '') ?></textarea>
        </div>
        <button type="submit">Save</button>
    </form>
</div>