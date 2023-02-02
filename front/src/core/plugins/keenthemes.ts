import {
  MenuComponent,
  ScrollComponent,
  StickyComponent,
  ToggleComponent,
  DrawerComponent,
  SwapperComponent,
  PasswordMeterComponent,
} from "@/assets/ts/components";

/**
 * @description Initialize KeenThemes custom components
 */
const initializeComponents = () => {
  setTimeout(() => {
    ToggleComponent.bootstrap();
    StickyComponent.bootstrap();
    MenuComponent.bootstrap();
    ScrollComponent.bootstrap();
    DrawerComponent.bootstrap();
    SwapperComponent.bootstrap();
    PasswordMeterComponent.bootstrap();
  }, 0);
};

/**
 * @description Reinitialize KeenThemes custom components
 */
const reinitializeComponents = () => {
  setTimeout(() => {
    ToggleComponent.reInitialization();
    StickyComponent.reInitialization();
    MenuComponent.reInitialization();
    reinitializeScrollComponent().then(() => {
      ScrollComponent.updateAll();
    });
    DrawerComponent.reInitialization();
    SwapperComponent.reInitialization();
    PasswordMeterComponent.reInitialization();
  }, 0);
};

const reinitializeScrollComponent = async () => {
  await ScrollComponent.reInitialization();
};

export {
  initializeComponents,
  reinitializeComponents,
  reinitializeScrollComponent,
};
