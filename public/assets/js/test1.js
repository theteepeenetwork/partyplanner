const predefinedServices = [
    {
        title: "Elegant LED Dance Floor",
        short_description: "A stunning centerpiece for any event.",
        description: "Make your event unforgettable with our state-of-the-art LED dance floor. Featuring customizable colors and patterns, this dance floor adds a dazzling touch to weddings, corporate events, and private parties. Easy to install and highly durable, it creates a vibrant atmosphere where your guests can dance the night away. Perfect for adding glamour and sophistication to any venue.",
        service_tags: "dance, LED, wedding",
        category_id: 1,
        subcategory_id: 2,
        third_category_id: 3,
        price: 250,
        images: ["image1.jpg", "image2.jpg", "image3.jpg"],
        extras: [
            { extraName: "Custom Colours", extraDescription: "Choose a colour scheme that matches your event theme.", extraPrice: 50 },
            { extraName: "Extended Rental Period", extraDescription: "Keep the dance floor for an additional day.", extraPrice: 100 },
            { extraName: "Setup Assistance", extraDescription: "On-site staff to assist with setup and breakdown.", extraPrice: 75 }
        ]
    },
    {
        title: "Giant LOVE Letters",
        short_description: "Create the perfect romantic atmosphere.",
        description: "Our giant LOVE letters are the perfect addition to weddings, engagements, and romantic events. Standing tall with bright LED lighting, these letters create a captivating backdrop for photos and add a magical ambiance to any venue. Easy to transport and set up, they can be placed indoors or outdoors to suit your event's needs. Let these elegant letters set the mood for love and celebration.",
        service_tags: "wedding, decoration, love",
        category_id: 1,
        subcategory_id: 4,
        third_category_id: 5,
        price: 150,
        images: ["image4.jpg", "image5.jpg"],
        extras: [
            { extraName: "Floral Garland", extraDescription: "Add a decorative floral garland to the letters.", extraPrice: 25 },
            { extraName: "Outdoor Setup", extraDescription: "Weatherproof setup for outdoor events.", extraPrice: 30 },
            { extraName: "Custom Letter Colours", extraDescription: "Choose colours to match your event theme.", extraPrice: 20 }
        ]
    },
    {
        title: "Vintage Photo Booth",
        short_description: "Capture timeless memories.",
        description: "Add a touch of nostalgia to your event with our vintage photo booth. Perfect for weddings, parties, and corporate events, this photo booth comes with a variety of fun props and instant printouts for your guests to enjoy. The stylish design and high-quality camera ensure that every memory is captured in stunning detail. Easy to set up and use, this booth is guaranteed to be a hit with guests of all ages.",
        service_tags: "photo, booth, vintage",
        category_id: 2,
        subcategory_id: 6,
        third_category_id: 7,
        price: 300,
        images: ["image6.jpg", "image7.jpg"],
        extras: [
            { extraName: "Custom Backdrop", extraDescription: "Personalised backdrop with event name or logo.", extraPrice: 50 },
            { extraName: "Digital Copy of Photos", extraDescription: "Receive all photos on a USB or via cloud link.", extraPrice: 30 },
            { extraName: "Extra Printouts", extraDescription: "Unlimited printouts for guests during the event.", extraPrice: 40 }
        ]
    },
    {
        title: "Luxury Wedding Car",
        short_description: "Arrive in style.",
        description: "Arrive at your wedding or special occasion in ultimate luxury with our premium car service. Choose from a selection of classic and modern vehicles, including limousines, vintage cars, and luxury sedans. Our professional drivers ensure a smooth and comfortable ride, making your journey as memorable as the event itself. Perfect for adding a touch of elegance and sophistication to your special day.",
        service_tags: "wedding, car, transport",
        category_id: 3,
        subcategory_id: 8,
        third_category_id: 9,
        price: 500,
        images: ["image8.jpg", "image9.jpg"],
        extras: [
            { extraName: "Champagne Package", extraDescription: "Includes chilled champagne and glasses.", extraPrice: 75 },
            { extraName: "Decorated Car", extraDescription: "Custom ribbons and flowers to match your theme.", extraPrice: 50 },
            { extraName: "Extended Hours", extraDescription: "Additional hire time for the car.", extraPrice: 100 }
        ]
    },
    {
        title: "Live Jazz Band",
        short_description: "Live music to elevate your event.",
        description: "Set the perfect mood for your event with our professional live jazz band. Featuring talented musicians with years of experience, our band can play a wide range of jazz styles, from smooth and sultry to upbeat and lively. Ideal for weddings, corporate events, and private parties, their captivating performances will leave a lasting impression on your guests.",
        service_tags: "music, jazz, band",
        category_id: 4,
        subcategory_id: 10,
        third_category_id: 11,
        price: 1000,
        images: ["image10.jpg"],
        extras: [
            { extraName: "Extended Performance", extraDescription: "Add an extra hour of music.", extraPrice: 200 },
            { extraName: "Custom Playlist", extraDescription: "Request specific songs or styles.", extraPrice: 100 },
            { extraName: "Meet and Greet", extraDescription: "Guests can meet the band after the performance.", extraPrice: 50 }
        ]
    },
    {
        title: "Elegant Chair Covers",
        short_description: "Enhance your seating arrangements.",
        description: "Transform your venue's seating with our elegant chair covers. Available in a variety of colors and styles, these covers are designed to complement any event theme. Whether you're hosting a wedding, gala, or corporate event, our chair covers provide a polished and sophisticated look. Easy to install and made from high-quality materials, they add a touch of elegance to any setting.",
        service_tags: "chair, covers, decoration",
        category_id: 5,
        subcategory_id: 12,
        third_category_id: 13,
        price: 75,
        images: ["image11.jpg", "image12.jpg"],
        extras: [
            { extraName: "Custom Colours", extraDescription: "Chair covers in colours of your choice.", extraPrice: 20 },
            { extraName: "Sashes", extraDescription: "Add matching or contrasting sashes for extra style.", extraPrice: 15 },
            { extraName: "Setup Service", extraDescription: "Our team will install and remove the covers.", extraPrice: 30 }
        ]
    },
    {
        title: "Chocolate Fountain",
        short_description: "Indulge your guests.",
        description: "Treat your guests to a decadent chocolate fountain experience. Perfect for weddings, parties, and corporate events, our fountain comes with a variety of dippable treats like strawberries, marshmallows, and pretzels. Made from high-quality stainless steel, it ensures smooth and consistent chocolate flow, creating an indulgent centerpiece for your dessert table.",
        service_tags: "chocolate, fountain, dessert",
        category_id: 6,
        subcategory_id: 14,
        third_category_id: 15,
        price: 200,
        images: ["image13.jpg"],
        extras: [
            { extraName: "Additional Dips", extraDescription: "Extra treats such as brownies and fruit skewers.", extraPrice: 30 },
            { extraName: "White Chocolate Option", extraDescription: "Replace milk chocolate with white chocolate.", extraPrice: 25 },
            { extraName: "Extra Servings", extraDescription: "Increase the quantity to serve more guests.", extraPrice: 50 }
        ]
    },
    {
        title: "Vintage Carnival Games",
        short_description: "Bring classic fun to your event.",
        description: "Our vintage carnival games include ring toss, bean bag throw, and duck fishing. These games are perfect for fairs, birthday parties, and outdoor events. Easy to set up and entertaining for guests of all ages, they add a nostalgic charm and ensure hours of fun.",
        service_tags: "games, carnival, vintage",
        category_id: 7,
        subcategory_id: 16,
        third_category_id: 17,
        price: 150,
        images: ["image14.jpg", "image15.jpg"],
        extras: [
            { extraName: "Additional Games", extraDescription: "Include more games for larger events.", extraPrice: 50 },
            { extraName: "Prizes", extraDescription: "Provide small prizes for game winners.", extraPrice: 30 },
            { extraName: "Attendant", extraDescription: "Hire an attendant to manage the games.", extraPrice: 75 }
        ]
    },
    {
        title: "Luxury Marquee",
        short_description: "Host your event in style.",
        description: "Our luxury marquees provide a spacious and elegant setting for weddings, parties, and corporate events. Available in various sizes, they include lighting, flooring, and weatherproofing. Customizable to fit your theme and equipped with modern amenities, these marquees ensure comfort and style.",
        service_tags: "marquee, luxury, event",
        category_id: 8,
        subcategory_id: 18,
        third_category_id: 19,
        price: 1200,
        images: ["image16.jpg", "image17.jpg"],
        extras: [
            { extraName: "Heating", extraDescription: "Provide heaters for colder weather.", extraPrice: 100 },
            { extraName: "Decor Package", extraDescription: "Include decorative elements to match your theme.", extraPrice: 150 },
            { extraName: "Catering Area", extraDescription: "Additional space for catering setup.", extraPrice: 200 }
        ]
    },
    {
        title: "Mobile Bar Service",
        short_description: "Professional bar service on the go.",
        description: "Our mobile bar service brings a fully stocked bar and professional bartenders to your event. Offering a wide selection of drinks, including cocktails, beer, and wine, this service is ideal for weddings, parties, and corporate gatherings. Customizable drink menus and themed setups are available.",
        service_tags: "bar, drinks, mobile",
        category_id: 9,
        subcategory_id: 20,
        third_category_id: 21,
        price: 500,
        images: ["image18.jpg", "image19.jpg"],
        extras: [
            { extraName: "Custom Cocktails", extraDescription: "Create signature cocktails for your event.", extraPrice: 50 },
            { extraName: "Extended Hours", extraDescription: "Additional bar service time.", extraPrice: 100 },
            { extraName: "Themed Bar Setup", extraDescription: "Decorate the bar to match your theme.", extraPrice: 75 }
        ]
    },
    {
        title: "Inflatable Bounce House",
        short_description: "Fun for kids and adults alike.",
        description: "Add excitement to your event with our inflatable bounce house. Perfect for birthday parties, fairs, and family events, this inflatable attraction provides hours of entertainment. Easy to set up and suitable for all ages.",
        service_tags: "inflatable, bounce, kids",
        category_id: 10,
        subcategory_id: 22,
        third_category_id: 23,
        price: 300,
        images: ["image20.jpg", "image21.jpg"],
        extras: [
            { extraName: "Themed Bounce House", extraDescription: "Choose a theme such as princess, jungle, or pirate.", extraPrice: 50 },
            { extraName: "Attendant", extraDescription: "Hire an attendant to monitor safety.", extraPrice: 75 },
            { extraName: "Extended Rental Period", extraDescription: "Keep the bounce house for an additional day.", extraPrice: 100 }
        ]
    },
    {
        title: "Outdoor Movie Screen",
        short_description: "A cinematic experience under the stars.",
        description: "Host a movie night or presentation with our outdoor movie screen package. Includes a high-quality projector, sound system, and weatherproof screen. Ideal for family gatherings, corporate events, or community movie nights.",
        service_tags: "movie, outdoor, screen",
        category_id: 11,
        subcategory_id: 24,
        third_category_id: 25,
        price: 400,
        images: ["image22.jpg"],
        extras: [
            { extraName: "Popcorn Station", extraDescription: "Provide fresh popcorn for your guests.", extraPrice: 50 },
            { extraName: "Additional Speakers", extraDescription: "Enhance audio for larger audiences.", extraPrice: 75 },
            { extraName: "Extended Rental Time", extraDescription: "Keep the screen for a late-night show.", extraPrice: 100 }
        ]
    },
    {
        title: "Interactive Magic Show",
        short_description: "Engage and amaze your guests.",
        description: "Bring the magic to your event with a professional magician performing interactive tricks and illusions. Perfect for birthdays, weddings, and corporate events, this show captivates audiences of all ages, leaving them spellbound.",
        service_tags: "magic, entertainment, show",
        category_id: 12,
        subcategory_id: 26,
        third_category_id: 27,
        price: 350,
        images: ["image23.jpg"],
        extras: [
            { extraName: "Close-Up Magic", extraDescription: "Add close-up magic for intimate guest interaction.", extraPrice: 100 },
            { extraName: "Themed Performance", extraDescription: "Customize the magic show to fit your event theme.", extraPrice: 50 },
            { extraName: "Extended Show", extraDescription: "Additional 30 minutes of performance.", extraPrice: 75 }
        ]
    },
    {
        title: "Themed Photo Props",
        short_description: "Enhance your event photography.",
        description: "Add fun and creativity to your event photos with our themed prop packages. Suitable for photobooths or photographers, these props are tailored to fit various themes, from weddings to corporate events.",
        service_tags: "props, photo, themed",
        category_id: 13,
        subcategory_id: 28,
        third_category_id: 29,
        price: 100,
        images: ["image24.jpg", "image25.jpg"],
        extras: [
            { extraName: "Custom Props", extraDescription: "Personalized props with names or logos.", extraPrice: 50 },
            { extraName: "Prop Stand", extraDescription: "Provide a stand to neatly display props.", extraPrice: 25 },
            { extraName: "Themed Backdrop", extraDescription: "Matching backdrop for your chosen theme.", extraPrice: 75 }
        ]
    },
    {
        title: "Luxury Portable Restrooms",
        short_description: "Comfort and convenience for your guests.",
        description: "Provide high-end portable restrooms for outdoor events. Equipped with modern amenities such as air conditioning, lighting, and sanitation supplies, these restrooms ensure comfort and hygiene for all guests.",
        service_tags: "restroom, portable, luxury",
        category_id: 14,
        subcategory_id: 30,
        third_category_id: 31,
        price: 600,
        images: ["image26.jpg"],
        extras: [
            { extraName: "Attendant Service", extraDescription: "On-site staff for maintenance during the event.", extraPrice: 100 },
            { extraName: "Additional Units", extraDescription: "Add more restrooms for larger events.", extraPrice: 150 },
            { extraName: "Custom Branding", extraDescription: "Personalized branding on the restroom units.", extraPrice: 200 }
        ]
    },
    {
        title: "Artisanal Coffee Cart",
        short_description: "A gourmet coffee experience.",
        description: "Delight your guests with premium coffee brewed on-site by professional baristas. Our artisanal coffee cart is perfect for weddings, conferences, and parties, offering a variety of coffee and tea options.",
        service_tags: "coffee, cart, gourmet",
        category_id: 15,
        subcategory_id: 32,
        third_category_id: 33,
        price: 400,
        images: ["image27.jpg"],
        extras: [
            { extraName: "Custom Cups", extraDescription: "Personalized cups with event branding.", extraPrice: 50 },
            { extraName: "Expanded Menu", extraDescription: "Include specialty drinks like frappes and smoothies.", extraPrice: 75 },
            { extraName: "Additional Barista", extraDescription: "Hire an extra barista for faster service.", extraPrice: 100 }
        ]
    },
    {
        title: "Custom Balloon Installations",
        short_description: "Decorate with style and creativity.",
        description: "Transform your venue with stunning balloon installations. From arches to walls, our custom designs suit any event theme and create a festive atmosphere.",
        service_tags: "balloons, decoration, custom",
        category_id: 16,
        subcategory_id: 34,
        third_category_id: 35,
        price: 300,
        images: ["image28.jpg", "image29.jpg"],
        extras: [
            { extraName: "Themed Colors", extraDescription: "Choose colors to match your theme.", extraPrice: 50 },
            { extraName: "LED Balloons", extraDescription: "Add lighting to your balloon setup.", extraPrice: 75 },
            { extraName: "Delivery and Setup", extraDescription: "Ensure the balloons are ready on time.", extraPrice: 100 }
        ]
    }
];




document.addEventListener("DOMContentLoaded", () => {
    // Create a select element
    const selectElement = document.createElement("select");
    selectElement.id = "extrasSelect";
    selectElement.classList.add("form-control", "mb-3");

    // Add a default option
    const defaultOption = document.createElement("option");
    defaultOption.value = "";
    defaultOption.textContent = "Select a Service";
    selectElement.appendChild(defaultOption);

    // Add services to the select dropdown
    predefinedServices.forEach((service, index) => {
        const option = document.createElement("option");
        option.value = index; // Store service index
        option.textContent = service.title; // Display service title
        selectElement.appendChild(option);
    });

    // Insert select above the form
    const form = document.getElementById("publicEventForm");
    if (form) {
        form.parentNode.insertBefore(selectElement, form);
    } else {
        console.error("Form with id 'publicEventForm' not found.");
        return;
    }

    // Handle selection
    selectElement.addEventListener("change", (event) => {
        const selectedIndex = event.target.value;
        if (selectedIndex !== "") {
            const selectedService = predefinedServices[selectedIndex];

            // Populate optional extras
            populateOptionalExtras(selectedService.extras);
        }
    });

    // Function to populate optional extras dynamically
    function populateOptionalExtras(extras) {
        extras.forEach((extra, index) => {
            const extraCount = index + 1;

            // Find existing fields
            const extraNameField = document.getElementById(`extra_name_${extraCount}`);
            const extraDescriptionField = document.getElementById(`extra_description_${extraCount}`);
            const extraPriceField = document.getElementById(`extra_price_${extraCount}`);

            if (extraNameField) {
                extraNameField.value = extra.extraName || "";
            }

            if (extraDescriptionField) {
                extraDescriptionField.value = extra.extraDescription || "";
            }

            if (extraPriceField) {
                extraPriceField.value = extra.extraPrice || 0;
            }
        });
    }
});

